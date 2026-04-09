import requests

BASE_URL = "http://localhost:80/MUJSTAYS"
TIMEOUT = 30

def test_post_verify_email_with_valid_otp_and_session():
    session = requests.Session()
    signup_url = f"{BASE_URL}/signup.php"
    verify_email_url = f"{BASE_URL}/verify-email.php"

    # Test user signup with a unique email to get a session cookie and is_verified=0
    signup_data = {
        "role": "student",
        "name": "Test User TC003",
        "email": "tc003_user@example.com",
        "password": "Password123!"
    }

    try:
        # Signup the user
        signup_resp = session.post(signup_url, data=signup_data, timeout=TIMEOUT)
        assert signup_resp.status_code == 201, f"Signup failed with status {signup_resp.status_code}"
        signup_json = signup_resp.json()
        assert "is_verified" in signup_json, "Signup response missing is_verified"
        assert signup_json["is_verified"] == 0, "User should not be verified after signup"

        # Verify email with valid OTP 123456 as configured in verify-email.php
        verify_data = {
            "otp": "123456"
        }
        verify_resp = session.post(verify_email_url, data=verify_data, timeout=TIMEOUT)
        assert verify_resp.status_code == 200, f"Email verification failed with status {verify_resp.status_code}"
        verify_json = verify_resp.json()
        assert "is_verified" in verify_json, "Verify email response missing is_verified"
        assert verify_json["is_verified"] == 1, "User should be verified after OTP verification"

    finally:
        # Cleanup: delete user if an endpoint existed - PRD does not show a delete user endpoint
        # So no deletion step here.

        # Close session
        session.close()

test_post_verify_email_with_valid_otp_and_session()