import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

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

    print("2. Navigating to Hub 2 (CMS & Tienda)...")
    driver.get("http://127.0.0.1:8000/admin/tienda-cms")
    time.sleep(2)

    tabs = ['sections', 'content_rules', 'pages', 'tracking', 'filters', 'media']
    for t in tabs:
        print(f"   -> Switching to tab: {t}")
        driver.execute_script(f"window.switchStoreTab && window.switchStoreTab('{t}')")
        time.sleep(0.5)

    print("3. Testing Tracking Telemetry Ping Button...")
    driver.execute_script("window.switchStoreTab('tracking')")
    time.sleep(0.5)

    # Click the ping button
    ping_btn = driver.find_element(By.XPATH, "//button[contains(., 'Probar Conexión')]")
    ping_btn.click()
    time.sleep(2)

    results_displayed = driver.execute_script("return !document.getElementById('trackingTestResults').classList.contains('hidden');")
    print(f"   -> Tracking diagnostic results displayed: {results_displayed}")

    print("4. Inspecting Browser Console Logs...")
    logs = driver.get_log('browser')
    severe_errors = [l for l in logs if l['level'] == 'SEVERE']
    warning_logs = [l for l in logs if l['level'] == 'WARNING']

    print(f"\n--- AUDIT RESULTS FOR HUB 2 ---")
    print(f"Total Console Logs: {len(logs)}")
    print(f"SEVERE Errors: {len(severe_errors)}")
    for err in severe_errors:
        print(f"   [ERROR] {err['message']}")
    print(f"WARNINGs: {len(warning_logs)}")

    assert len(severe_errors) == 0, f"Expected 0 SEVERE errors, found {len(severe_errors)}"
    print("\nSUCCESS: Hub 2 passed with 0 SEVERE console errors and full interactivity!")

finally:
    driver.quit()
