import requests
from datetime import datetime

BASE_URL = "http://localhost:80/MUJSTAYS"
TIMEOUT = 30

def test_post_user_bookings_php_manage_bookings():
    session = requests.Session()

    # Step 1: Signup a new student user
    signup_data = {
        "role": "student",
        "name": "Test User Booking",
        "email": f"testuserbooking_{int(datetime.utcnow().timestamp())}@example.com",
        "password": "Password123!"
    }
    resp = session.post(f"{BASE_URL}/signup.php", data=signup_data, timeout=TIMEOUT)
    assert resp.status_code == 201, f"Signup failed: {resp.status_code} {resp.text}"
    json_resp = resp.json()
    assert "is_verified" in json_resp and json_resp["is_verified"] == 0, "User should be unverified initially"

    # Step 2: Verify email with OTP bypass = 123456
    verify_data = {
        "otp": "123456"
    }
    resp = session.post(f"{BASE_URL}/verify-email.php", data=verify_data, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Email verification failed: {resp.status_code} {resp.text}"
    json_resp = resp.json()
    assert "is_verified" in json_resp and json_resp["is_verified"] == 1, "is_verified should be 1 after verification"

    # Step 3: Login user to establish authenticated session
    login_data = {
        "email": signup_data["email"],
        "password": signup_data["password"]
    }
    resp = session.post(f"{BASE_URL}/login.php", data=login_data, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Login failed: {resp.status_code} {resp.text}"

    # Step 4: Prepare booking data with valid pg_id, room_type_id, move_in_date, booking_type
    booking_data = {
        "pg_id": "123",
        "room_type_id": "5",
        "move_in_date": "2026-05-10",
        "booking_type": "request"
    }

    try:
        resp = session.post(f"{BASE_URL}/user/bookings.php", data=booking_data, timeout=TIMEOUT)
        assert resp.status_code == 201, f"Booking creation failed: {resp.status_code} {resp.text}"
        json_resp = resp.json()
        assert "status" in json_resp, "Response JSON missing booking status"
        assert json_resp["status"] == "pending", f"Unexpected booking status: {json_resp['status']}"
        assert "booking_id" in json_resp, "Response JSON missing booking_id"
    finally:
        # No cleanup as no API documented for deleting bookings by student
        pass

test_post_user_bookings_php_manage_bookings()
