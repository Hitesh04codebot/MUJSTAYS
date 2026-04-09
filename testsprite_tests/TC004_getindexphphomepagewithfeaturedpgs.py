import requests

def test_get_index_php_homepage_with_featured_pgs():
    base_url = "http://localhost:80/MUJSTAYS"
    url = f"{base_url}/index.php"
    timeout = 30

    try:
        response = requests.get(url, timeout=timeout)
        assert response.status_code == 200, f"Expected 200 OK, got {response.status_code}"

        content = response.text

        # Check presence of featured PG listing indication (e.g. is_featured=1) in HTML response
        assert "is_featured=1" in content or "featured" in content.lower(), "Featured PG listings not found in homepage content"

        # Check presence of site hero content keyword
        assert any(keyword in content.lower() for keyword in ["hero", "site hero", "banner", "promo"]), "Site hero content not found in homepage content"

    except requests.RequestException as e:
        assert False, f"Request failed: {str(e)}"

test_get_index_php_homepage_with_featured_pgs()