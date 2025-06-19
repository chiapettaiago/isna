#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para corrigir orientação de PDFs com rotação de 90 graus no sentido anti-horário (270 graus)
Autor: GitHub Copilot
Data: 19 de Junho de 2025
"""

import os
import tempfile
import shutil
import PyPDF2
from pdf2image import convert_from_path
from PIL import Image
import logging
import sys

# Configuração de logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("pdf_orientation_fix.log"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger()

# Configurações
INPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
OUTPUT_DIR = INPUT_DIR
DPI = 600  # Alta resolução
QUALITY = 100  # Máxima qualidade
ROTATION_ANGLE = 90  # 90 graus = girar 90° no sentido horário

# Lista específica de documentos para corrigir
DOCS_TO_FIX = [
    "CertificadoSiconv.pdf",
    "cmas.pdf",
    "Titulo de Utilidade publica.pdf"
]

def fix_pdf_orientation(pdf_path):
    """Corrige a orientação do PDF aplicando rotação de 270 graus"""
    pdf_name = os.path.basename(pdf_path)
    logger.info(f"Corrigindo orientação de {pdf_name} com rotação de {ROTATION_ANGLE} graus")
    
    # Criar diretório temporário único para este arquivo
    with tempfile.TemporaryDirectory() as temp_path:
        try:
            # Converter PDF para imagens
            logger.info("Convertendo PDF para imagens em alta resolução...")
            images = convert_from_path(
                pdf_path, 
                dpi=DPI,
                output_folder=temp_path,
                fmt="png",
                thread_count=os.cpu_count(),
                use_cropbox=True
            )
            
            # Aplicar rotação a cada página
            rotated_images = []
            for i, img in enumerate(images):
                logger.info(f"Rotacionando página {i+1}/{len(images)}")
                # Rotação de 270 graus (90 graus no sentido anti-horário)
                rotated = img.rotate(ROTATION_ANGLE, expand=True, resample=Image.BICUBIC)
                rotated_images.append(rotated)
                
            # Salvar imagens rotacionadas como um novo PDF
            temp_output = os.path.join(temp_path, "fixed_" + pdf_name)
            if rotated_images:
                logger.info("Salvando PDF com orientação corrigida...")
                rotated_images[0].save(
                    temp_output,
                    "PDF",
                    resolution=DPI,
                    save_all=True,
                    append_images=rotated_images[1:],
                    quality=QUALITY,
                    optimize=False,
                    dpi=(DPI, DPI)
                )
                
                # Copiar metadados do PDF original para o novo
                transfer_pdf_metadata(pdf_path, temp_output)
                
                # Substituir o arquivo original pelo corrigido
                output_path = os.path.join(OUTPUT_DIR, pdf_name)
                shutil.copy2(temp_output, output_path)
                logger.info(f"PDF corrigido salvo: {output_path}")
                return True
            else:
                logger.warning(f"Nenhuma imagem gerada para {pdf_name}")
                return False
                
        except Exception as e:
            logger.error(f"Erro ao processar {pdf_name}: {str(e)}")
            return False

def transfer_pdf_metadata(original_pdf, new_pdf):
    """Transfere metadados do PDF original para o novo"""
    try:
        # Abrir os PDFs
        with open(original_pdf, 'rb') as orig_file, open(new_pdf, 'rb') as new_file:
            orig_reader = PyPDF2.PdfReader(orig_file)
            new_reader = PyPDF2.PdfReader(new_file)
            
            # Criar um escritor para o PDF final
            writer = PyPDF2.PdfWriter()
            
            # Adicionar páginas do novo PDF
            for page in new_reader.pages:
                writer.add_page(page)
            
            # Copiar metadados do original se disponíveis
            if orig_reader.metadata is not None:
                writer.add_metadata(dict(orig_reader.metadata))
            
            # Salvar o resultado em um arquivo temporário
            with tempfile.NamedTemporaryFile(delete=False, suffix='.pdf') as temp_file:
                temp_path = temp_file.name
            
            with open(temp_path, 'wb') as output_file:
                writer.write(output_file)
            
            # Substituir o novo PDF pelo com metadados
            shutil.move(temp_path, new_pdf)
            
    except Exception as e:
        logger.error(f"Erro ao transferir metadados: {str(e)}")

def main():
    """Função principal"""
    logger.info(f"Iniciando correção de orientação de PDFs com rotação de {ROTATION_ANGLE} graus")
    
    # Processar cada documento da lista
    for doc in DOCS_TO_FIX:
        pdf_path = os.path.join(INPUT_DIR, doc)
        
        if not os.path.exists(pdf_path):
            logger.warning(f"Arquivo não encontrado: {doc}")
            continue
        
        # Corrigir orientação
        success = fix_pdf_orientation(pdf_path)
        
        if success:
            print(f"Orientação de {doc} corrigida com sucesso!")
        else:
            print(f"Falha ao corrigir orientação de {doc}")
    
    logger.info("Processamento concluído!")

if __name__ == "__main__":
    main()
