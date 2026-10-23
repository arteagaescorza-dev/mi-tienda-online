from PIL import Image

img_path = r"C:\Users\Mickey\.gemini\antigravity\brain\b1cb36f8-714f-4e0a-b018-32629ea4d2b6\.user_uploaded\media_1792721531912_7d2fc290.png"
im = Image.open(img_path)
print("Image size:", im.size)

# Crop the header and tabs area (roughly y from 100 to 350, full width)
# im.size is likely around (1920, 1080) or similar
w, h = im.size
crop1 = im.crop((0, int(h * 0.12), w, int(h * 0.35)))
crop1.save(r"C:\laragon\www\mi-tienda-online\scratch\cropped_tabs.png")
print("Saved cropped_tabs.png")

