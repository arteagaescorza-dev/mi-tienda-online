import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By

options = webdriver.ChromeOptions()
options.add_argument('--headless=new')
options.add_argument('--disable-gpu')
options.add_argument('--no-sandbox')
options.add_argument('--window-size=1440,900')

driver = webdriver.Chrome(options=options)

try:
    print("1. Logging into RevOps Panel...")
    driver.get("http://127.0.0.1:8000/login")
    time.sleep(1)

    email_input = driver.find_element(By.NAME, "email")
    pass_input = driver.find_element(By.NAME, "password")
    email_input.send_keys("admin@tienda.test")
    pass_input.send_keys("password")

    submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
    submit_btn.click()
    time.sleep(2)

    print("2. Navigating to Hub 1 (Dashboard Operativo)...")
    driver.get("http://127.0.0.1:8000/admin/dashboard")
    time.sleep(2)

    page_title = driver.title
    print(f"   -> Page title: {page_title}")

    # Test tab switches (Executive, Operative, Sales)
    tabs = ['executive', 'operative', 'sales']
    for t in tabs:
        print(f"   -> Switching to main subtab: {t}")
        driver.execute_script(f"""
            const el = document.querySelector('[x-data*="dashboardModule"]');
            if (el && window.Alpine) {{
                const data = Alpine.$data(el);
                data.switchTab('{t}');
            }}
        """)
        time.sleep(0.5)

    # In Operative tab, test toggle between Almacen and Compras
    print("3. Testing Operative Area Segregation (Almacen vs Compras)...")
    driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        if (el && window.Alpine) {
            const data = Alpine.$data(el);
            data.switchTab('operative');
            data.switchOperativeArea('almacen');
        }
    """)
    time.sleep(0.5)
    area = driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        return (el && window.Alpine) ? Alpine.$data(el).operativeArea : null;
    """)
    print(f"   -> Operative area set to: {area}")

    driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        if (el && window.Alpine) {
            const data = Alpine.$data(el);
            data.switchOperativeArea('compras');
        }
    """)
    time.sleep(0.5)
    area = driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        return (el && window.Alpine) ? Alpine.$data(el).operativeArea : null;
    """)
    print(f"   -> Operative area switched to: {area}")

    # Check DHL 14:00 timer / status
    dhl_status = driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        return (el && window.Alpine) ? Alpine.$data(el).dhlStatusColor : null;
    """)
    dhl_countdown = driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        return (el && window.Alpine) ? Alpine.$data(el).dhlCountdown : null;
    """)
    print(f"   -> DHL 14:00 SLA status color: {dhl_status}, countdown: {dhl_countdown}")

    print("4. Inspecting Browser Console Logs...")
    logs = driver.get_log('browser')
    severe_errors = [l for l in logs if l['level'] == 'SEVERE']
    print(f"   -> Total console messages: {len(logs)}")
    print(f"   -> SEVERE errors: {len(severe_errors)}")
    for err in severe_errors:
        print(f"      [SEVERE] {err['message']}")

    if len(severe_errors) == 0:
        print("\nSUCCESS: Hub 1 Dashboard Operativo & Sub-Areas passed headless navigation with 0 severe errors!")
    else:
        print(f"\nWARNING: Found {len(severe_errors)} severe errors.")

finally:
    driver.quit()
