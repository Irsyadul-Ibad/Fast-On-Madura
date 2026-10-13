from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(r"C:\Users\Ibad\OneDrive\Dokumen\magang\program\Fast-On")
OUT = ROOT / "report_assets"
FONT = r"C:\Windows\Fonts\consola.ttf"


def make_image(name, source, start, end, title):
    lines = source.read_text(encoding="utf-8").splitlines()[start - 1:end]
    font = ImageFont.truetype(FONT, 22)
    title_font = ImageFont.truetype(FONT, 26)
    line_height = 30
    width = 1900
    height = 70 + line_height * len(lines) + 35
    image = Image.new("RGB", (width, height), "#1e2430")
    draw = ImageDraw.Draw(image)
    draw.text((28, 18), title, fill="#f8d66d", font=title_font)
    for offset, line in enumerate(lines):
        number = start + offset
        y = 70 + offset * line_height
        draw.text((28, y), f"{number:>4}  {line}", fill="#e6edf3", font=font)
    image.save(OUT / name)


make_image(
    "code-routes-upload.png",
    ROOT / "routes" / "web.php",
    137,
    161,
    "routes/web.php - autentikasi, permission, dan upload PB/PD",
)
make_image(
    "code-permissions.png",
    ROOT / "database" / "seeders" / "PermissionSeeder.php",
    41,
    84,
    "PermissionSeeder.php - pemetaan hak akses role",
)
make_image(
    "code-import-excel.png",
    ROOT / "app" / "Imports" / "PelangganPbpdImport.php",
    18,
    67,
    "PelangganPbpdImport.php - pemrosesan data Excel PB/PD",
)
print("created_code_screenshots=3")
