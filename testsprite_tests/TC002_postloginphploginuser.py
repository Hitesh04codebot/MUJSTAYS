import requests

BASE_URL = "http://localhost:80/MUJSTAYS"
TIMEOUT = 30

def test_post_login_php_login_user():
    """
    Test user login with valid credentials, expect 200 response and session established.
    """
    session = requests.Session()
    login_url = f"{BASE_URL}/login.php"

    # These credentials must be valid existing user credentials in the system.
    # Since PRD does not provide specific test accounts, using example.
    # Adjust email and password accordingly before running the test.
    login_payload = {
        "email": "student@example.com",
        "password": "ValidPassword123"
    }

    try:
        response = session.post(login_url, data=login_payload, timeout=TIMEOUT)
        # Assert response code
        assert response.status_code == 200, f"Expected 200 OK but got {response.status_code}"

        # Assert session cookie set (PHPSESSID typical)
        assert any(cookie.name.lower() == "phpsessid" for cookie in session.cookies), "Session cookie not set"

        # Optionally check response content if relevant (e.g. redirect or success message)
        # Since PRD does not specify response body for login success, skipping body assertions

    except requests.RequestException as e:
        assert False, f"HTTP request failed: {e}"

test_post_login_php_login_user()