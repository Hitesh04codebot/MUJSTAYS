import requests

BASE_URL = "http://localhost:80/MUJSTAYS"
TIMEOUT = 30

def test_getpgdetailphpviewpgdetails():
    # Since the test case requires a valid PG id for the pg-detail.php page,
    # and PRD does not provide a method to create a PG listing easily,
    # we try to find a valid PG id by hitting the index page or explore page.
    # Otherwise, if no PG id found, the test cannot proceed meaningfully.

    session = requests.Session()

    try:
        # Attempt to get a featured PG id from the home page or explore page
        # First, try index.php (home page)
        resp_index = session.get(f"{BASE_URL}/index.php", timeout=TIMEOUT)
        assert resp_index.status_code == 200
        # Try to extract PG id from response text using a simple heuristic for links like pg-detail.php?id=123
        import re
        matches = re.findall(r"pg-detail\.php\?id=(\d+)", resp_index.text)
        pg_id = None
        if matches:
            pg_id = matches[0]

        # If no PG id from index.php, try explore.php with no filters (or minimal)
        if not pg_id:
            resp_explore = session.get(f"{BASE_URL}/explore.php?page=1", timeout=TIMEOUT)
            assert resp_explore.status_code == 200
            matches = re.findall(r"pg-detail\.php\?id=(\d+)", resp_explore.text)
            if matches:
                pg_id = matches[0]

        # If still no PG id found, fail test
        assert pg_id is not None, "No valid PG id found on index.php or explore.php pages"

        # Now get the PG detail page with the found valid pg_id
        resp_pg_detail = session.get(f"{BASE_URL}/pg-detail.php", params={"id": pg_id}, timeout=TIMEOUT)
        assert resp_pg_detail.status_code == 200, f"Expected 200, got {resp_pg_detail.status_code}"

        content = resp_pg_detail.text

        # Validate that the response HTML contains expected PG info:
        # Basic checks for presence of PG details, gallery, room types and owner summary keywords
        assert "gallery" in content.lower() or "images" in content.lower() or "photos" in content.lower()
        assert "room" in content.lower()  # room types info
        assert "owner" in content.lower() and ("summary" in content.lower() or "profile" in content.lower())

    except Exception as e:
        raise
    finally:
        session.close()

test_getpgdetailphpviewpgdetails()