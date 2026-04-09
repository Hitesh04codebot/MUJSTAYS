import requests
import json
import re

BASE_URL = "http://localhost:80/MUJSTAYS"
TIMEOUT = 30

OWNER_EMAIL = "owner@mujstays.com"
OWNER_PASSWORD = "Owner@1234"

def test_post_owner_add_listing():
    session = requests.Session()
    try:
        # GET login page to extract csrf_token
        login_page_resp = session.get(f"{BASE_URL}/login.php", timeout=TIMEOUT)
        assert login_page_resp.status_code == 200, f"Failed to load login page: {login_page_resp.text}"
        m = re.search(r'name="csrf_token" value="([a-f0-9]+)"', login_page_resp.text)
        assert m, "csrf_token not found in login page"
        csrf_token = m.group(1)

        # Login owner to get session cookie including csrf_token
        login_resp = session.post(
            f"{BASE_URL}/login.php",
            data={"email": OWNER_EMAIL, "password": OWNER_PASSWORD, "csrf_token": csrf_token},
            timeout=TIMEOUT,
        )
        assert login_resp.status_code == 200, f"Owner login failed: {login_resp.text}"

        # Prepare data for adding new listing
        title = "Test PG Listing Title"
        description = "This is a test description for a new PG listing."
        address = "123 Test Street, Test City, Test State"
        room_types = [
            {
                "type": "single",
                "available_beds": 5,
                "rent": 6000,
                "deposit": 12000,
                "amenities": ["wifi", "ac", "furnished"]
            },
            {
                "type": "double",
                "available_beds": 3,
                "rent": 10000,
                "deposit": 20000,
                "amenities": ["wifi", "heater", "balcony"]
            }
        ]
        room_types_json = json.dumps(room_types)

        # Prepare multipart form data with photos (simulate photo upload using dummy bytes)
        # Minimum 3 photos as per requirement
        files = {
            "photos[]": (
                "photo1.jpg",
                b"dummyimagecontent1",
                "image/jpeg"
            ),
            "photos[]_2": (
                "photo2.jpg",
                b"dummyimagecontent2",
                "image/jpeg"
            ),
            "photos[]_3": (
                "photo3.jpg",
                b"dummyimagecontent3",
                "image/jpeg"
            )
        }

        data = {
            "title": title,
            "description": description,
            "address": address,
            "room_types": room_types_json
        }

        # Post add-listing.php
        add_listing_resp = session.post(
            f"{BASE_URL}/owner/add-listing.php",
            data=data,
            files=files,
            timeout=TIMEOUT,
        )

        assert add_listing_resp.status_code == 201, f"Failed to add listing: {add_listing_resp.text}"
        resp_json = add_listing_resp.json()
        assert "listing_id" in resp_json, "Response missing listing_id"
        assert resp_json.get("status") == "pending", f"Expected status pending, got {resp_json.get('status')}"

    finally:
        # Cleanup: delete the created listing if listing_id returned
        try:
            listing_id = resp_json.get("listing_id")
        except Exception:
            listing_id = None
        if listing_id:
            try:
                del_resp = session.post(
                    f"{BASE_URL}/owner/listings.php",
                    data={"action": "delete", "listing_id": listing_id},
                    timeout=TIMEOUT,
                )
            except Exception:
                pass

test_post_owner_add_listing()
