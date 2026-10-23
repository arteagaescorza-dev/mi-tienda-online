import time
import json
from selenium import webdriver
from selenium.webdriver.common.by import By

options = webdriver.ChromeOptions()
options.add_argument('--headless=new')
options.add_argument('--disable-gpu')
options.add_argument('--no-sandbox')
options.add_argument('--window-size=1440,900')
options.page_load_strategy = 'eager'

driver = webdriver.Chrome(options=options)
driver.set_page_load_timeout(20)

try:
    print("=================================================================")
    print("REVOPS PANEL AUDIT: FINAL END-TO-END VERIFICATION (HUBS 1,2,4,5,7)")
    print("=================================================================")

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

    hubs = [
        {"name": "Hub 1 (Dashboard Operativo)", "url": "http://127.0.0.1:8000/admin/dashboard", "expected_el": "#dashboardAppRoot"},
        {"name": "Hub 2 (CMS & Tienda Real)", "url": "http://127.0.0.1:8000/admin/tienda-cms", "expected_el": "#storeAppRoot"},
        {"name": "Hub 4 (Inventario Híbrido & Curaduría)", "url": "http://127.0.0.1:8000/admin/inventario-catalogo", "expected_el": "[x-data*='hybridInventoryModule']"},
        {"name": "Hub 5 (Finanzas & Conciliación)", "url": "http://127.0.0.1:8000/admin/finanzas", "expected_el": "#financeAppRoot"},
        {"name": "Hub 7 (Convenios & Aliados B2B)", "url": "http://127.0.0.1:8000/admin/convenios", "expected_el": "#alliesAppRoot"},
    ]

    total_severe_errors = 0

    for hub in hubs:
        print(f"\nVerifying {hub['name']}...")
        driver.get(hub['url'])
        time.sleep(1.5)

        title = driver.title
        print(f"   -> Page Title: {title}")

        # Check element existence
        exists = driver.execute_script(f"""
            return document.querySelector("{hub['expected_el']}") !== null;
        """)
        print(f"   -> Root Element '{hub['expected_el']}' present: {exists}")
        assert exists, f"Element {hub['expected_el']} not found on {hub['url']}"

        # Browser logs
        logs = driver.get_log('browser')
        severe = [l for l in logs if l['level'] == 'SEVERE']
        print(f"   -> Console logs: {len(logs)} entries, {len(severe)} SEVERE errors")
        for err in severe:
            print(f"      [SEVERE] {err['message']}")
        total_severe_errors += len(severe)

    print("\n=================================================================")
    if total_severe_errors == 0:
        print("ALL 5 AUDITED HUBS (1, 2, 4, 5, 7) PASSED COMPLETE E2E VERIFICATION WITH 0 SEVERE CONSOLE ERRORS!")
    else:
        print(f"WARNING: {total_severe_errors} severe console errors detected across hubs.")
    print("=================================================================")

finally:
    driver.quit()

