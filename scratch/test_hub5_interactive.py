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

    print("2. Navigating to Hub 5 (Finanzas & Conciliación)...")
    driver.get("http://127.0.0.1:8000/admin/finanzas")
    time.sleep(2)

    page_title = driver.title
    print(f"   -> Page title: {page_title}")

    # Tabs to test in Hub 5
    tabs = ['cashflow', 'reconciliation', 'subscriptions', 'margins', 'expenses', 'shipping_audit']
    for t in tabs:
        print(f"   -> Testing switch to tab: {t}")
        driver.execute_script(f"""
            const btn = document.querySelector('[data-finance-tab="{t}"]');
            if (btn) btn.click();
        """)
        time.sleep(0.5)
        is_visible = driver.execute_script(f"""
            const panel = document.querySelector('[data-finance-panel="{t}"]');
            return panel ? !panel.classList.contains('hidden') : false;
        """)
        print(f"      Panel [data-finance-panel='{t}'] visible: {is_visible}")
        assert is_visible, f"Panel for tab {t} should be visible"

    print("3. Inspecting SPEI safeguards in Cuentas por Pagar & Cotejo 3 Vías...")
    driver.execute_script("""
        const btn = document.querySelector('[data-finance-tab="expenses"]');
        if (btn) btn.click();
    """)
    time.sleep(0.5)

    safeguards = driver.execute_script("""
        const spans = Array.from(document.querySelectorAll('span'));
        const blockedBadges = spans.filter(s => s.textContent.includes('SPEI Retenido') || s.textContent.includes('SPEI Bloqueado'));
        return {
            hasExpensesPanel: !document.querySelector('[data-finance-panel="expenses"]').classList.contains('hidden'),
            blockedCount: blockedBadges.length
        };
    """)
    print(f"   -> Expenses panel active: {safeguards['hasExpensesPanel']}")

    print("4. Inspecting Browser Console Logs...")
    logs = driver.get_log('browser')
    severe_errors = [l for l in logs if l['level'] == 'SEVERE']
    print(f"   -> Total console messages: {len(logs)}")
    print(f"   -> SEVERE errors: {len(severe_errors)}")
    for err in severe_errors:
        print(f"      [SEVERE] {err['message']}")

    if len(severe_errors) == 0:
        print("\nSUCCESS: Hub 5 Finanzas & Conciliación passed headless navigation with 0 severe errors!")
    else:
        print(f"\nWARNING: {len(severe_errors)} severe errors detected.")

finally:
    driver.quit()

