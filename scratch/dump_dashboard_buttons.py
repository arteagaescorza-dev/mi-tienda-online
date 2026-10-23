import time
from selenium import webdriver
from selenium.webdriver.common.by import By

options = webdriver.ChromeOptions()
options.add_argument('--headless=new')
options.page_load_strategy = 'eager'

driver = webdriver.Chrome(options=options)
try:
    driver.get("http://127.0.0.1:8000/login")
    time.sleep(1)
    driver.find_element(By.NAME, "email").send_keys("admin@tienda.test")
    driver.find_element(By.NAME, "password").send_keys("password")
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(2)

    driver.get("http://127.0.0.1:8000/admin/dashboard")
    time.sleep(2)

    # Let's inspect all buttons on the page
    buttons = driver.find_elements(By.TAG_NAME, "button")
    print(f"Total buttons on dashboard: {len(buttons)}")
    for b in buttons[:40]:
        text = b.text.replace('\n', ' ')
        classes = b.get_attribute("class")
        if text.strip():
            print(f"BUTTON: '{text.strip()}' | classes: {classes[:50]}")
finally:
    driver.quit()

