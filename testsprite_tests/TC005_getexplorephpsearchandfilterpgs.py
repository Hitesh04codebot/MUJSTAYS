import requests

BASE_URL = "http://localhost:80/MUJSTAYS"

def test_get_explore_php_search_and_filter_pgs():
    params = {
        "area": "Jagatpura",
        "price_max": "8000",
        "room_type": "single",
        "page": "1"
    }
    try:
        response = requests.get(f"{BASE_URL}/explore.php", params=params, timeout=30)
        assert response.status_code == 200, f"Expected status code 200, got {response.status_code}"
        content_type = response.headers.get('Content-Type', '')
        assert 'application/json' in content_type.lower(), f"Expected JSON response but got Content-Type: {content_type}"
        text = response.text.strip()
        assert text != '', "Response body is empty"
        data = response.json()
        
        # Check presence of keys related to filtered results and pagination metadata
        assert "results" in data, "Response JSON missing 'results' key"
        assert isinstance(data["results"], list), "'results' should be a list"
        
        assert "pagination" in data, "Response JSON missing 'pagination' key"
        pagination = data["pagination"]
        assert "page" in pagination and pagination["page"] == 1, "Pagination page mismatch or missing"
        assert "total_pages" in pagination and isinstance(pagination["total_pages"], int), "Pagination total_pages missing or invalid"
        assert "total_results" in pagination and isinstance(pagination["total_results"], int), "Pagination total_results missing or invalid"
        
        # Verify each result matches filter criteria as much as possible by keys if present
        for pg in data["results"]:
            if "area" in pg:
                assert "Jagatpura".lower() in pg["area"].lower(), f"PG area {pg['area']} does not match filter 'Jagatpura'"
            if "price" in pg:
                try:
                    price = int(pg["price"])
                    assert price <= 8000, f"PG price {price} exceeds filter max price 8000"
                except:
                    pass
            if "room_types" in pg:
                match_room_type = False
                for rt in pg["room_types"]:
                    if "type" in rt and rt["type"].lower() == "single":
                        match_room_type = True
                        break
                assert match_room_type, "No room_type 'single' found in PG room_types"
    except requests.RequestException as e:
        assert False, f"Request to explore.php failed: {e}"
    except ValueError as e:
        assert False, f"JSON decoding failed: {e}"

test_get_explore_php_search_and_filter_pgs()