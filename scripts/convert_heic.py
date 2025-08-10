#!/usr/bin/env python3
"""
Conversão idempotente de HEIC/HEIF para JPG e WebP.
- Procura recursivamente por *.heic/*.heif no diretório informado (padrão: images/recital)
- Para cada arquivo, cria <base>.jpg (auto-orient) e <base>.webp (qualidade 82) se ainda não existirem
Uso:
  python scripts/convert_heic.py [diretorio]
Exemplos:
  python scripts/convert_heic.py              # converte images/recital
  python scripts/convert_heic.py images       # converte em toda a pasta images
"""

import sys
import os
import argparse
from pathlib import Path
from typing import Iterable

try:
    from PIL import Image
    import pillow_heif  # type: ignore
except Exception as e:
    print("Dependências ausentes. Instale com: pip install Pillow pillow-heif", file=sys.stderr)
    raise


def iter_heic_files(root: Path) -> Iterable[Path]:
    for ext in (".heic", ".HEIC", ".heif", ".HEIF"):
        yield from root.rglob(f"*{ext}")


def ensure_parent(p: Path) -> None:
    p.parent.mkdir(parents=True, exist_ok=True)


def convert_one(src: Path) -> None:
    base = src.with_suffix("")
    jpg = base.with_suffix(".jpg")
    webp = base.with_suffix(".webp")

    # Registrar handler HEIF para Pillow
    pillow_heif.register_heif_opener()

    # Converter para JPG se necessário
    if not jpg.exists():
        with Image.open(src) as im:
            # auto-orient via EXIF
            try:
                exif = im.getexif()
                orientation = exif.get(0x0112)
                if orientation == 3:
                    im = im.rotate(180, expand=True)
                elif orientation == 6:
                    im = im.rotate(270, expand=True)
                elif orientation == 8:
                    im = im.rotate(90, expand=True)
            except Exception:
                pass
            ensure_parent(jpg)
            im.convert("RGB").save(jpg, "JPEG", quality=90, optimize=True)
            print(f"[JPG] {jpg}")

    # Converter para WEBP se tiver JPG pronto e ainda não existir WEBP
    if jpg.exists() and not webp.exists():
        with Image.open(jpg) as im:
            ensure_parent(webp)
            im.save(webp, "WEBP", quality=82, method=6)
            print(f"[WEBP] {webp}")


def main() -> int:
    parser = argparse.ArgumentParser(description="Converter HEIC/HEIF em JPG e WebP (idempotente)")
    parser.add_argument("diretorio", nargs="?", default="images/recital", help="Diretório raiz (default: images/recital)")
    args = parser.parse_args()

    root = Path(args.diretorio).resolve()
    if not root.exists():
        print(f"Diretório não encontrado: {root}", file=sys.stderr)
        return 2

    count = 0
    for src in iter_heic_files(root):
        try:
            convert_one(src)
            count += 1
        except Exception as e:
            print(f"Erro convertendo {src}: {e}", file=sys.stderr)
    print(f"Concluído. Arquivos HEIC/HEIF processados: {count}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
