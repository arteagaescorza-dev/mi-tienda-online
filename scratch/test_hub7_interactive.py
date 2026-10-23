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

    print("2. Navigating to Hub 7 (Convenios & Aliados B2B)...")
    driver.get("http://127.0.0.1:8000/admin/convenios")
    time.sleep(2)

    page_title = driver.title
    print(f"   -> Page title: {page_title}")

    # Tabs and their corresponding DOM panel IDs
    tab_map = {
        'directory': 'alliesDirectoryPanel',
        'requests': 'alliesRequestsPanel',
        'campaigns_a': 'alliesCampaignsPanel',
        'attribution_b': 'alliesAttributionPanel',
        'clearing': 'alliesClearingPanel',
        'partner_portal': 'alliesPartnerPortalPanel',
    }

    for t, panel_id in tab_map.items():
        print(f"   -> Testing switch to tab: {t} (panel: {panel_id})")
        driver.execute_script(f"window.switchTab('{t}');")
        time.sleep(0.5)
        is_visible = driver.execute_script(f"""
            const panel = document.getElementById('{panel_id}');
            return panel ? !panel.classList.contains('hidden') : false;
        """)
        print(f"      Panel {panel_id} visible: {is_visible}")
        assert is_visible, f"Panel {panel_id} should be visible"

    print("3. Checking Clearing House SPEI CLABE safeguards...")
    driver.execute_script("window.switchTab('clearing');")
    time.sleep(0.5)

    clabe_status = driver.execute_script("""
        const speiBtns = document.querySelectorAll('button[name="settlement_type"][value="spei_transfer"]');
        let disabledCount = 0;
        let enabledCount = 0;
        speiBtns.forEach(b => {
            if (b.disabled) disabledCount++;
            else enabledCount++;
        });
        return { total: speiBtns.length, disabled: disabledCount, enabled: enabledCount };
    """)
    print(f"   -> SPEI settlement buttons audited: {clabe_status}")

    print("4. Inspecting Browser Console Logs...")
    logs = driver.get_log('browser')
    severe_errors = [l for l in logs if l['level'] == 'SEVERE']
    print(f"   -> Total console messages: {len(logs)}")
    print(f"   -> SEVERE errors: {len(severe_errors)}")
    for err in severe_errors:
        print(f"      [SEVERE] {err['message']}")

    if len(severe_errors) == 0:
        print("\nSUCCESS: Hub 7 Convenios & Aliados passed headless navigation with 0 severe errors!")
    else:
        print(f"\nWARNING: {len(severe_errors)} severe errors detected.")

finally:
    driver.quit()

