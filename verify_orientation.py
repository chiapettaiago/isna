#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Verificador de orientação de PDFs
Este script gera imagens em miniatura a partir de PDFs para verificar visualmente a orientação
Autor: GitHub Copilot
Data: 19 de Junho de 2025
"""

import os
import sys
from pdf2image import convert_from_path
from PIL import Image, ImageDraw, ImageFont
import logging

# Configuração de logging
logging.basicConfig(level=logging.INFO, 
                    format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger()

# Configurações
INPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
THUMBNAIL_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "thumbnails")
DPI = 72  # Baixa resolução para miniaturas

# Lista específica de documentos para verificar
DOCS_TO_CHECK = [
    "CertificadoSiconv.pdf",
    "cmas.pdf",
    "Titulo de Utilidade publica.pdf"
]

def ensure_dir(directory):
    """Garante que o diretório existe"""
    if not os.path.exists(directory):
        os.makedirs(directory)
        logger.info(f"Diretório criado: {directory}")

def create_thumbnail(pdf_path):
    """Cria uma miniatura do PDF para verificação visual"""
    pdf_name = os.path.basename(pdf_path)
    name_without_ext = os.path.splitext(pdf_name)[0]
    
    # Garantir que o diretório de saída exista
    ensure_dir(THUMBNAIL_DIR)
    
    try:
        # Converter apenas a primeira página do PDF para uma imagem
        logger.info(f"Criando miniatura para {pdf_name}...")
        images = convert_from_path(
            pdf_path, 
            dpi=DPI,
            first_page=1,
            last_page=1
        )
        
        if images:
            # Redimensionar para uma miniatura padrão
            thumbnail_size = (600, 800)
            thumbnail = images[0].copy()
            thumbnail.thumbnail(thumbnail_size, Image.LANCZOS)
            
            # Adicionar título com o nome do arquivo
            draw = ImageDraw.Draw(thumbnail)
            try:
                font = ImageFont.truetype("Arial", 16)
            except IOError:
                font = ImageFont.load_default()
                
            draw.text((10, 10), pdf_name, fill=(0, 0, 0), font=font)
            
            # Salvar a miniatura
            thumbnail_path = os.path.join(THUMBNAIL_DIR, f"{name_without_ext}_thumbnail.png")
            thumbnail.save(thumbnail_path, "PNG")
            logger.info(f"Miniatura salva em: {thumbnail_path}")
            
            return thumbnail_path
        else:
            logger.warning(f"Não foi possível criar miniatura para {pdf_name}")
            return None
            
    except Exception as e:
        logger.error(f"Erro ao processar {pdf_name}: {str(e)}")
        return None

def main():
    """Função principal"""
    logger.info("Iniciando verificação de orientação de PDFs")
    
    # Criar diretório para miniaturas
    ensure_dir(THUMBNAIL_DIR)
    
    # Processar cada documento da lista
    thumbnails = []
    for doc in DOCS_TO_CHECK:
        pdf_path = os.path.join(INPUT_DIR, doc)
        
        if not os.path.exists(pdf_path):
            logger.warning(f"Arquivo não encontrado: {doc}")
            continue
        
        # Criar miniatura
        thumbnail_path = create_thumbnail(pdf_path)
        if thumbnail_path:
            thumbnails.append(thumbnail_path)
    
    # Criar uma imagem combinada com todas as miniaturas
    if thumbnails:
        try:
            # Abrir todas as miniaturas
            images = [Image.open(path) for path in thumbnails]
            
            # Determinar o tamanho da imagem combinada
            max_width = max(img.width for img in images)
            total_height = sum(img.height for img in images)
            
            # Criar uma nova imagem com fundo branco
            combined = Image.new('RGB', (max_width, total_height), color='white')
            
            # Colar cada miniatura
            y_offset = 0
            for img in images:
                combined.paste(img, (0, y_offset))
                y_offset += img.height
            
            # Salvar a imagem combinada
            combined_path = os.path.join(THUMBNAIL_DIR, "all_documents.png")
            combined.save(combined_path)
            logger.info(f"Imagem combinada salva em: {combined_path}")
            print(f"\nMiniaturas criadas em: {THUMBNAIL_DIR}")
            print(f"Verifique a imagem combinada para confirmar a orientação: {combined_path}")
        except Exception as e:
            logger.error(f"Erro ao criar imagem combinada: {str(e)}")
    
    logger.info("Verificação concluída!")

if __name__ == "__main__":
    main()
