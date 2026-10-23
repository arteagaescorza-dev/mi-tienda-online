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
    print("1. Logging into RevOps Portal...")
    driver.get("http://127.0.0.1:8000/login")
    time.sleep(1)

    email_input = driver.find_element(By.NAME, "email")
    pass_input = driver.find_element(By.NAME, "password")
    email_input.send_keys("admin@tienda.test")
    pass_input.send_keys("password")

    submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
    submit_btn.click()
    time.sleep(2)

    print("2. Navigating to Hub 1 (Dashboard General / Ejecutivo)...")
    driver.get("http://127.0.0.1:8000/admin/dashboard")
    time.sleep(2)

    # Ensure main executive tab is active
    driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        if (el && window.Alpine) {
            Alpine.$data(el).switchTab('executive');
        }
    """)
    time.sleep(1)

    subtabs = [
        ('revops', 'Comercial & RevOps', 'btn-subtab-revops', 'panel-subtab-revops'),
        ('inventory', 'Almacén e Inventario', 'btn-subtab-inventory', 'panel-subtab-inventory'),
        ('store', 'Vitrinas & Tienda', 'btn-subtab-store', 'panel-subtab-store'),
        ('crm', 'Marketing & CRM', 'btn-subtab-crm', 'panel-subtab-crm'),
        ('finance', 'Finanzas & Tesorería', 'btn-subtab-finance', 'panel-subtab-finance'),
        ('logistics', 'Logística & Envíos', 'btn-subtab-logistics', 'panel-subtab-logistics'),
        ('allies', 'Convenios & B2B', 'btn-subtab-allies', 'panel-subtab-allies')
    ]

    print("3. Testing Level 2 Departmental Navigation (7 Subtabs)...")
    for tab_id, tab_label, btn_id, panel_id in subtabs:
        btn = driver.find_element(By.ID, btn_id)
        assert btn is not None, f"Button {btn_id} not found in DOM!"
        btn.click()
        time.sleep(0.5)

        active_tab = driver.execute_script("""
            const el = document.querySelector('[x-data*="dashboardModule"]');
            return (el && window.Alpine) ? Alpine.$data(el).activeExecutiveSubTab : null;
        """)
        assert active_tab == tab_id, f"Expected activeExecutiveSubTab to be {tab_id}, got {active_tab}"

        panel = driver.find_element(By.ID, panel_id)
        is_displayed = panel.is_displayed()
        assert is_displayed, f"Panel {panel_id} should be visible when {tab_id} is active"
        print(f"   -> [OK] Subtab '{tab_label}' ({tab_id}) activated and visible.")

    # 4. In Inventory subtab, test time range and Top 10 modal
    print("4. Testing Inventory Subtab Interactions...")
    driver.find_element(By.ID, "btn-subtab-inventory").click()
    time.sleep(0.5)

    driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        if (el && window.Alpine) {
            Alpine.$data(el).setExecutiveInventoryTimeRange('7d');
        }
    """)
    time.sleep(0.3)
    curr_range = driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        return (el && window.Alpine) ? Alpine.$data(el).executiveInventoryTimeRange : null;
    """)
    assert curr_range == '7d', f"Expected time range '7d', got {curr_range}"
    print(f"   -> [OK] Inventory time range set to: {curr_range}")

    print("5. Testing Top 10 Modal Opening...")
    driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        if (el && window.Alpine) {
            Alpine.$data(el).openTopTenModal();
        }
    """)
    time.sleep(0.5)
    modal_active = driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        return (el && window.Alpine) ? Alpine.$data(el).activeModal : null;
    """)
    assert modal_active == 'top_ten', f"Expected activeModal == 'top_ten', got {modal_active}"
    print(f"   -> [OK] Top 10 modal opened successfully.")

    driver.execute_script("""
        const el = document.querySelector('[x-data*="dashboardModule"]');
        if (el && window.Alpine) {
            Alpine.$data(el).closeModal();
        }
    """)
    time.sleep(0.3)

    print("6. Inspecting Browser Console Logs...")
    logs = driver.get_log('browser')
    severe_errors = [l for l in logs if l['level'] == 'SEVERE']
    print(f"   -> Total console messages: {len(logs)}")
    print(f"   -> SEVERE errors: {len(severe_errors)}")
    for err in severe_errors:
        print(f"      [SEVERE] {err['message']}")

    assert len(severe_errors) == 0, f"Found {len(severe_errors)} severe console errors!"

    print("\nSUCCESS: All 7 departmental executive views in Hub 1 verified with 0 console errors!")

finally:
    driver.quit()
