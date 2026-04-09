import requests

BASE_URL = "http://localhost:80/MUJSTAYS"
TIMEOUT = 30

def test_post_signupphp_create_new_account():
    session = requests.Session()
    signup_url = f"{BASE_URL}/signup.php"
    verify_email_url = f"{BASE_URL}/verify-email.php"

    signup_payload = {
        "role": "student",
        "name": "Test Student",
        "email": "teststudent_signup@example.com",
        "password": "StrongPass!123"
    }

    try:
        # Step 1: Signup user
        signup_resp = session.post(signup_url, data=signup_payload, timeout=TIMEOUT)
        assert signup_resp.status_code == 201, f"Expected 201 Created but got {signup_resp.status_code}"
        # Check for session cookie presence
        assert any(cookie.name.startswith("PHPSESSID") for cookie in session.cookies), "Session cookie not set after signup"
        # Validate is_verified=0 in response JSON or text
        try:
            signup_json = signup_resp.json()
            assert signup_json.get("is_verified") == 0, f"Expected is_verified=0 but got {signup_json.get('is_verified')}"
        except Exception:
            # Fallback: look for is_verified=0 in response text
            assert "is_verified=0" in signup_resp.text or '"is_verified":0' in signup_resp.text, "is_verified=0 not found in signup response"

        # Step 2: Verify email using OTP bypass (123456)
        verify_payload = {"otp": "123456"}
        verify_resp = session.post(verify_email_url, data=verify_payload, timeout=TIMEOUT)
        assert verify_resp.status_code == 200, f"Expected 200 OK for email verification but got {verify_resp.status_code}"
        # Validate is_verified=1 in verify_email.php response
        try:
            verify_json = verify_resp.json()
            assert verify_json.get("is_verified") == 1, f"Expected is_verified=1 after email verification but got {verify_json.get('is_verified')}"
        except Exception:
            assert "is_verified=1" in verify_resp.text or '"is_verified":1' in verify_resp.text, "is_verified=1 not found in email verification response"

    finally:
        # Cleanup: There is no endpoint given to delete the user. Usually, in real tests, we would delete test data here.
        # Since not specified, no delete call implemented.
        pass

test_post_signupphp_create_new_account()