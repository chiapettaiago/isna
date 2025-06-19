#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para visualizar e testar diferentes rotações em documentos PDF
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
import argparse
import subprocess

# Configuração de logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("pdf_rotation_test.log"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger()

# Configurações
INPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
OUTPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "temp_rotations")

def ensure_dir(directory):
    """Garante que o diretório existe"""
    if not os.path.exists(directory):
        os.makedirs(directory)
        logger.info(f"Diretório criado: {directory}")

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

def test_rotations(pdf_path, angles=[0, 90, 180, 270]):
    """
    Cria versões de teste do PDF com diferentes rotações
    
    Args:
        pdf_path: Caminho para o PDF a ser rotacionado
        angles: Lista de ângulos para rotação (padrão: 0, 90, 180, 270 graus)
    """
    pdf_name = os.path.basename(pdf_path)
    name_without_ext = os.path.splitext(pdf_name)[0]
    
    logger.info(f"Testando rotações para {pdf_name}")
    
    # Garantir que o diretório de saída existe
    ensure_dir(OUTPUT_DIR)
    
    # Usar tempfile para criar um diretório temporário
    with tempfile.TemporaryDirectory() as temp_path:
        try:
            # Converter PDF para imagens
            logger.info("Convertendo PDF para imagens...")
            images = convert_from_path(
                pdf_path, 
                dpi=200,  # Menor DPI para visualização rápida
                output_folder=temp_path,
                fmt="jpeg"
            )
            
            # Criar uma versão para cada ângulo
            for angle in angles:
                logger.info(f"Criando versão rotacionada em {angle} graus")
                
                # Rotacionar cada página
                rotated_images = []
                for i, img in enumerate(images):
                    # Aplicar rotação
                    rotated = img.rotate(angle, expand=True)
                    rotated_images.append(rotated)
                
                # Salvar imagens rotacionadas como um novo PDF
                output_filename = f"{name_without_ext}_rotated_{angle}.pdf"
                output_path = os.path.join(OUTPUT_DIR, output_filename)
                
                if rotated_images:
                    logger.info(f"Salvando versão com rotação de {angle} graus...")
                    rotated_images[0].save(
                        output_path,
                        "PDF",
                        resolution=200,
                        save_all=True,
                        append_images=rotated_images[1:],
                        quality=85,  # Menor qualidade para arquivos de teste
                        optimize=True
                    )
                    
                    logger.info(f"Versão salva em: {output_path}")
            
            logger.info(f"Todas as versões rotacionadas foram criadas para {pdf_name}")
            return True
                
        except Exception as e:
            logger.error(f"Erro ao processar {pdf_name}: {str(e)}")
            return False

def open_pdf(pdf_path):
    """Tenta abrir o PDF com um visualizador padrão"""
    try:
        if sys.platform.startswith('darwin'):
            subprocess.run(['open', pdf_path])
        elif sys.platform.startswith('linux'):
            subprocess.run(['xdg-open', pdf_path])
        elif sys.platform.startswith('win'):
            os.startfile(pdf_path)
        else:
            logger.warning(f"Não foi possível abrir automaticamente o PDF no sistema {sys.platform}")
            return False
        return True
    except Exception as e:
        logger.error(f"Erro ao abrir o PDF: {str(e)}")
        return False

def main():
    """Função principal"""
    parser = argparse.ArgumentParser(description="Testa diferentes rotações em documentos PDF")
    parser.add_argument("filename", help="Nome do arquivo PDF para testar (deve estar na pasta docs)")
    parser.add_argument("--angles", type=int, nargs="+", default=[0, 90, 180, 270], 
                        help="Ângulos a testar (padrão: 0, 90, 180, 270)")
    parser.add_argument("--open", action="store_true", help="Abrir os PDFs após criar")
    args = parser.parse_args()
    
    logger.info(f"Iniciando teste de rotações para {args.filename}")
    
    # Verificar se o Poppler está instalado
    if not check_poppler_installed():
        sys.exit(1)
    
    # Verificar se o arquivo existe
    pdf_path = os.path.join(INPUT_DIR, args.filename)
    if not os.path.exists(pdf_path):
        logger.error(f"Arquivo não encontrado: {pdf_path}")
        sys.exit(1)
    
    # Criar versões rotacionadas
    if test_rotations(pdf_path, args.angles):
        logger.info(f"Versões de teste criadas com sucesso em {OUTPUT_DIR}")
        
        # Abrir os PDFs se solicitado
        if args.open:
            logger.info("Abrindo PDFs gerados...")
            name_without_ext = os.path.splitext(args.filename)[0]
            for angle in args.angles:
                output_path = os.path.join(OUTPUT_DIR, f"{name_without_ext}_rotated_{angle}.pdf")
                if os.path.exists(output_path):
                    if open_pdf(output_path):
                        logger.info(f"Aberto: {output_path}")
                    else:
                        logger.warning(f"Não foi possível abrir: {output_path}")
    
    logger.info("Processamento concluído!")

if __name__ == "__main__":
    main()
