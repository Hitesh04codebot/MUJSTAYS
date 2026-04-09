import requests

BASE_URL = "http://localhost:80/MUJSTAYS"
TIMEOUT = 30

ADMIN_EMAIL = "admin@mujstays.com"
ADMIN_PASSWORD = "Admin@1234"

OWNER_EMAIL = "owner@mujstays.com"
OWNER_PASSWORD = "Owner@1234"

import re

def get_csrf_token_from_html(html):
    match = re.search(r'name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']', html)
    if match:
        return match.group(1)
    return None


def test_postadminlistingsphpapproveorrejectlistings():
    session = requests.Session()
    session_owner = requests.Session()
    try:
        # Admin login: get csrf token
        resp_login_page = session.get(f"{BASE_URL}/login.php", timeout=TIMEOUT)
        assert resp_login_page.status_code == 200, f"Failed to get admin login page: {resp_login_page.text}"
        csrf_admin_login = get_csrf_token_from_html(resp_login_page.text)
        assert csrf_admin_login is not None, "Admin login CSRF token not found"

        resp_login = session.post(
            f"{BASE_URL}/login.php",
            data={"email": ADMIN_EMAIL, "password": ADMIN_PASSWORD, "csrf_token": csrf_admin_login},
            timeout=TIMEOUT,
        )
        assert resp_login.status_code == 200, f"Admin login failed: {resp_login.text}"

        # Owner login: get csrf token
        resp_owner_login_page = session_owner.get(f"{BASE_URL}/login.php", timeout=TIMEOUT)
        assert resp_owner_login_page.status_code == 200, f"Failed to get owner login page: {resp_owner_login_page.text}"
        csrf_owner_login = get_csrf_token_from_html(resp_owner_login_page.text)
        assert csrf_owner_login is not None, "Owner login CSRF token not found"

        resp_owner_login = session_owner.post(
            f"{BASE_URL}/login.php",
            data={"email": OWNER_EMAIL, "password": OWNER_PASSWORD, "csrf_token": csrf_owner_login},
            timeout=TIMEOUT,
        )
        assert resp_owner_login.status_code == 200, f"Owner login failed: {resp_owner_login.text}"

        # Get CSRF token from owner add-listing page (if needed)
        resp_add_listing_page = session_owner.get(f"{BASE_URL}/owner/add-listing.php", timeout=TIMEOUT)
        assert resp_add_listing_page.status_code == 200, f"Failed to get owner add-listing page: {resp_add_listing_page.text}"
        csrf_add_listing = get_csrf_token_from_html(resp_add_listing_page.text)
        assert csrf_add_listing is not None, "Add listing CSRF token not found"

        # Create a pending listing by owner (required to get a valid listing_id)
        # Minimal valid data for creating listing
        room_types = '[{"type":"single","available_beds":2,"price":5000}]'
        files = [
            ("photos[]", ("photo1.png", b"\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR", "image/png")),
            ("photos[]", ("photo2.png", b"\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR", "image/png")),
            ("photos[]", ("photo3.png", b"\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR", "image/png"))
        ]
        data_listing = {
            "title": "Test Listing",
            "description": "Test listing description",
            "address": "123 Test St, Test City",
            "room_types": room_types,
            "csrf_token": csrf_add_listing
        }
        resp_add_listing = session_owner.post(
            f"{BASE_URL}/owner/add-listing.php",
            data=data_listing,
            files=files,
            timeout=TIMEOUT,
        )
        assert resp_add_listing.status_code == 201, f"Add listing failed: {resp_add_listing.text}"

        # Extract listing_id from response JSON or text (expected to contain listing_id)
        try:
            listing_info = resp_add_listing.json()
            listing_id = listing_info.get("listing_id")
        except Exception:
            listing_id = None

        assert listing_id is not None, "Listing ID not found in add listing response"

        # Admin approves the created listing
        # Get CSRF token from admin listings page (if needed)
        resp_admin_listings_page = session.get(f"{BASE_URL}/admin/listings.php?status=pending", timeout=TIMEOUT)
        assert resp_admin_listings_page.status_code == 200, "Failed to get admin listings page for CSRF"
        csrf_admin_action = get_csrf_token_from_html(resp_admin_listings_page.text)
        if csrf_admin_action is None:
            # fallback: use login csrf or omit
            csrf_admin_action = csrf_admin_login

        data_approve = {
            "action": "approve",
            "listing_id": listing_id,
            "csrf_token": csrf_admin_action
        }
        resp_approve = session.post(
            f"{BASE_URL}/admin/listings.php",
            data=data_approve,
            timeout=TIMEOUT,
        )
        assert resp_approve.status_code == 200, f"Approve listing failed: {resp_approve.text}"

        # Validate response for updated listing status and owner notified
        try:
            approve_resp_json = resp_approve.json()
            status = approve_resp_json.get("status")
            owner_notified = approve_resp_json.get("owner_notified")
            assert status == "approved", "Listing status not updated to approved"
            assert owner_notified in [True, 1, "true", "True"], "Owner notification not sent"
        except Exception:
            # If response is not JSON, check for text confirmation
            assert "approved" in resp_approve.text.lower(), "Approval confirmation text missing"
            assert "notified" in resp_approve.text.lower(), "Owner notification confirmation missing"

    finally:
        # Cleanup: Delete the created listing if possible (assuming an endpoint exists)
        # No specific delete endpoint in PRD, skip delete or implement if exists
        pass

test_postadminlistingsphpapproveorrejectlistings()
