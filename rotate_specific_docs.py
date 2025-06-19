#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para rotacionar documentos específicos em 90 graus para a direita
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
        logging.FileHandler("specific_rotation.log"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger()

# Configurações
INPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
OUTPUT_DIR = INPUT_DIR
DPI = 600  # Resolução aumentada para melhor qualidade
QUALITY = 100  # Qualidade máxima de compressão JPEG
PRESERVE_ASPECT_RATIO = True  # Garantir que a proporção seja preservada

# Lista específica de documentos para rotacionar 0 graus (retornar à orientação original)
DOCS_TO_ROTATE = [
    "CertificadoSiconv.pdf",
    "cmas.pdf",
    "Titulo de Utilidade publica.pdf"
]

# Ângulo de rotação (0 graus para retornar à orientação original, antes das rotações)
ROTATION_ANGLE = 0

def check_poppler_installed():
    """Verifica se o Poppler está instalado"""
    try:
        import subprocess
        subprocess.run(
            ["pdftoppm", "-v"], 
            stdout=subprocess.PIPE, 
            stderr=subprocess.PIPE, 
            check=True
        )
        return True
    except (subprocess.SubprocessError, FileNotFoundError):
        logger.error("ERRO: Poppler não encontrado. Instale com 'sudo apt-get install poppler-utils'")
        return False

def rotate_pdf(pdf_path):
    """Rotaciona o PDF em 180 graus para corrigir documentos de cabeça para baixo"""
    pdf_name = os.path.basename(pdf_path)
    logger.info(f"Rotacionando {pdf_name} em 180 graus para corrigir orientação")
    
    # Usar tempfile para criar um diretório temporário
    with tempfile.TemporaryDirectory() as temp_path:
        try:
            # Converter PDF para imagens com alta qualidade
            logger.info("Convertendo PDF para imagens em alta resolução...")
            # Usando o formato PNG para evitar compressão JPEG que pode distorcer o documento
            images = convert_from_path(
                pdf_path, 
                dpi=DPI,
                output_folder=temp_path,
                fmt="png",  # Usar PNG para melhor qualidade
                thread_count=os.cpu_count(),
                use_cropbox=True,  # Usar cropbox para manter as dimensões corretas
                size=(None, None),  # Não redimensionar
                grayscale=False,  # Manter cores
                transparent=False  # Sem transparência para documentos
            )
            
            # Rotacionar cada página usando o ângulo definido e preservar proporção
            rotated_images = []
            for i, img in enumerate(images):
                logger.info(f"Processando página {i+1}/{len(images)}")
                
                # Obter dimensões originais
                original_width, original_height = img.size
                logger.info(f"Dimensões originais: {original_width}x{original_height}")
                
                # Rotação de 180 graus para inverter documentos de cabeça para baixo
                # Usando rotação de alta qualidade com interpolação bicúbica
                rotated = img.rotate(ROTATION_ANGLE, expand=True, resample=Image.BICUBIC)
                
                # Verificar se as dimensões foram preservadas após a rotação
                new_width, new_height = rotated.size
                logger.info(f"Dimensões após rotação: {new_width}x{new_height}")
                
                rotated_images.append(rotated)
                
            # Salvar imagens rotacionadas como um novo PDF
            temp_output = os.path.join(temp_path, "rotated_" + pdf_name)
            if rotated_images:
                logger.info("Salvando imagens rotacionadas como PDF...")
                # Salvar em PDF com configurações otimizadas para preservar qualidade
                rotated_images[0].save(
                    temp_output,
                    "PDF",
                    resolution=DPI,
                    save_all=True,
                    append_images=rotated_images[1:],
                    quality=QUALITY,
                    optimize=False,  # Desativar otimização automática para preservar qualidade
                    dpi=(DPI, DPI)  # Especificar DPI para X e Y para manter proporção
                )
                
                # Copiar metadados do PDF original para o novo
                transfer_pdf_metadata(pdf_path, temp_output)
                
                # Substituir o arquivo original pelo rotacionado
                output_path = os.path.join(OUTPUT_DIR, pdf_name)
                shutil.copy2(temp_output, output_path)
                logger.info(f"PDF rotacionado salvo: {output_path}")
            else:
                logger.warning(f"Nenhuma imagem gerada para {pdf_name}")
                
        except Exception as e:
            logger.error(f"Erro ao processar {pdf_name}: {str(e)}")

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
    logger.info(f"Iniciando rotação de documentos em {ROTATION_ANGLE} graus")
    
    # Verificar se o Poppler está instalado
    if not check_poppler_installed():
        sys.exit(1)
    
    # Verificar quais documentos existem
    files_to_process = []
    for doc in DOCS_TO_ROTATE:
        pdf_path = os.path.join(INPUT_DIR, doc)
        if os.path.exists(pdf_path):
            files_to_process.append(pdf_path)
        else:
            logger.warning(f"Arquivo não encontrado: {doc}")
    
    if not files_to_process:
        logger.error("Nenhum dos arquivos especificados foi encontrado.")
        sys.exit(1)
    
    # Rotacionar cada documento da lista
    for pdf_path in files_to_process:
        rotate_pdf(pdf_path)
    
    logger.info("Rotação de documentos concluída!")

if __name__ == "__main__":
    main()
