import requests
import re

BASE_URL = "http://localhost:80/MUJSTAYS"
TIMEOUT = 30

def test_get_user_chat_php_messaging_with_owners():
    session = requests.Session()
    try:
        # Step 0: Get signup page to retrieve CSRF token
        signup_page_resp = session.get(f"{BASE_URL}/signup.php", timeout=TIMEOUT)
        assert signup_page_resp.status_code == 200, f"Failed to load signup page: {signup_page_resp.text}"
        # Extract csrf_token hidden input value
        match = re.search(r'<input type="hidden" name="csrf_token" value="([a-f0-9]+)">', signup_page_resp.text)
        assert match, "CSRF token not found on signup page"
        csrf_token = match.group(1)

        # Step 1: Sign up a new student user
        signup_data = {
            "role": "student",
            "name": "Test Student",
            "email": "teststudent_tc008@example.com",
            "password": "Password123!",
            "confirm_password": "Password123!",
            "agree_terms": "on",
            "csrf_token": csrf_token
        }
        signup_resp = session.post(f"{BASE_URL}/signup.php", data=signup_data, timeout=TIMEOUT)
        assert signup_resp.status_code == 201, f"Signup failed: {signup_resp.text}"
        assert 'PHPSESSID' in session.cookies, "Session cookie missing after signup"
        # is_verified should be 0 initially
        json_signup = signup_resp.json()
        assert json_signup.get("is_verified") == 0, "User should not be verified after signup"

        # Step 2: Verify email with OTP=123456
        verify_data = {
            "otp": "123456"
        }
        verify_resp = session.post(f"{BASE_URL}/verify-email.php", data=verify_data, timeout=TIMEOUT)
        assert verify_resp.status_code == 200, f"Email verification failed: {verify_resp.text}"
        json_verify = verify_resp.json()
        assert json_verify.get("is_verified") == 1, "User should be verified after OTP"

        # Step 3: Login with the same credentials
        login_data = {
            "email": signup_data["email"],
            "password": signup_data["password"]
        }
        login_resp = session.post(f"{BASE_URL}/login.php", data=login_data, timeout=TIMEOUT)
        assert login_resp.status_code == 200, f"Login failed: {login_resp.text}"
        assert 'PHPSESSID' in session.cookies, "Session cookie missing after login"

        # Step 4: Create a chat conversation by sending a message to a known owner
        # To send message, we first need a pg_id and owner_id.
        # We'll try to get one from explore.php or index.php (without auth)
        exp_resp = requests.get(f"{BASE_URL}/explore.php?area=Jagatpura&price_max=8000&room_type=single&page=1", timeout=TIMEOUT)
        assert exp_resp.status_code == 200, f"Explore endpoint failed: {exp_resp.text}"
        pg_list = exp_resp.json().get("results", [])
        assert len(pg_list) > 0, "No PG listings found in explore"

        pg = pg_list[0]
        pg_id = pg.get("id") or pg.get("pg_id") or pg.get("pgId") or 0
        assert pg_id, "Invalid PG ID from explore"

        owner_id = pg.get("owner_id") or pg.get("ownerId") or 0
        assert owner_id, "Invalid owner ID from PG listing"

        message_text = "Is the room furnished?"
        send_msg_data = {
            "receiver_id": owner_id,
            "pg_id": pg_id,
            "message_text": message_text
        }
        send_resp = session.post(f"{BASE_URL}/user/chat.php", data=send_msg_data, timeout=TIMEOUT)
        assert send_resp.status_code == 201, f"Sending message failed: {send_resp.text}"
        send_json = send_resp.json()
        message_id = send_json.get("message_id") or send_json.get("id")
        assert message_id, "Message ID not returned after sending message"

        # Step 5: Retrieve chat conversation thread
        # Assuming the conversation_id is returned or linked to the message
        # If conversation_id not known, get from message or list conversations
        conversation_id = send_json.get("conversation_id")
        if not conversation_id:
            # fallback: get conversation_id by fetching chats for owner or last inserted message's conversation
            # Try to get active conversations by checking chat list
            # This may depend on API detail, but we try with message returns first
            conversation_id = send_json.get("conv_id") or send_json.get("conversationId")
        assert conversation_id, "Conversation ID not found after sending message"

        chat_resp = session.get(f"{BASE_URL}/user/chat.php", params={"conversation_id": conversation_id}, timeout=TIMEOUT)
        assert chat_resp.status_code == 200, f"Fetching chat failed: {chat_resp.text}"
        chat_json = chat_resp.json()
        assert isinstance(chat_json, dict), "Chat response is not a JSON object"
        assert "conversation" in chat_json or "messages" in chat_json, "Conversation thread JSON missing expected keys"

    finally:
        # Cleanup: No direct endpoint to delete user or messages mentioned
        # If possible, delete user by any provided API (not in PRD)
        pass

test_get_user_chat_php_messaging_with_owners()
