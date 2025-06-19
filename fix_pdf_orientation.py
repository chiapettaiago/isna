#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script interativo para corrigir orientação de PDFs.
Este script irá:
1. Verificar os documentos específicos
2. Para cada documento, criar 4 versões com rotações diferentes (0, 90, 180, 270 graus)
3. Solicitar ao usuário que escolha a orientação correta
4. Salvar o documento com a orientação escolhida

Autor: GitHub Copilot
Data: 19 de Junho de 2025
"""

import os
import tempfile
import shutil
import sys
import PyPDF2
from pdf2image import convert_from_path
from PIL import Image
import logging
import subprocess

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
TEMP_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "temp_orientation")
OUTPUT_DIR = INPUT_DIR  # Mesmo diretório de entrada
DPI = 300  # Resolução - ajustada para não sobrecarregar com arquivos temporários
QUALITY = 95  # Qualidade - ajustada para arquivos temporários

# Lista específica de documentos para verificar
DOCS_TO_FIX = [
    "CertificadoSiconv.pdf",
    "cmas.pdf",
    "Titulo de Utilidade publica.pdf"
]

def ensure_dir(directory):
    """Garante que o diretório existe"""
    if not os.path.exists(directory):
        os.makedirs(directory)
        logger.info(f"Diretório criado: {directory}")

def check_poppler_installed():
    """Verifica se o Poppler está instalado"""
    try:
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

def create_orientation_samples(pdf_path):
    """Cria 4 versões do documento com diferentes orientações"""
    pdf_name = os.path.basename(pdf_path)
    name_without_ext = os.path.splitext(pdf_name)[0]
    
    logger.info(f"Criando amostras de orientação para {pdf_name}")
    
    # Garantir que o diretório temporário exista
    ensure_dir(TEMP_DIR)
    
    # Usar tempfile para processamento
    with tempfile.TemporaryDirectory() as temp_path:
        try:
            # Converter PDF para imagens
            logger.info("Convertendo PDF para imagens...")
            images = convert_from_path(
                pdf_path, 
                dpi=DPI,
                output_folder=temp_path,
                fmt="png",
                thread_count=os.cpu_count()
            )
            
            sample_paths = []
            
            # Criar quatro versões com rotações diferentes
            for angle in [0, 90, 180, 270]:
                rotated_images = []
                for i, img in enumerate(images):
                    # Aplicar rotação
                    rotated = img.rotate(angle, expand=True, resample=Image.BICUBIC)
                    rotated_images.append(rotated)
                
                # Salvar imagens rotacionadas como um novo PDF
                sample_filename = f"{name_without_ext}_rotated_{angle}.pdf"
                sample_path = os.path.join(TEMP_DIR, sample_filename)
                
                if rotated_images:
                    logger.info(f"Salvando amostra com rotação de {angle} graus...")
                    rotated_images[0].save(
                        sample_path,
                        "PDF",
                        resolution=DPI,
                        save_all=True,
                        append_images=rotated_images[1:],
                        quality=QUALITY
                    )
                    
                    logger.info(f"Amostra salva em: {sample_path}")
                    sample_paths.append((angle, sample_path))
            
            return sample_paths
        
        except Exception as e:
            logger.error(f"Erro ao processar {pdf_name}: {str(e)}")
            return None

def fix_orientation(pdf_path, selected_angle):
    """Corrige a orientação do PDF com base no ângulo selecionado"""
    pdf_name = os.path.basename(pdf_path)
    
    logger.info(f"Corrigindo orientação de {pdf_name} com rotação de {selected_angle} graus")
    
    # Usar tempfile para processamento
    with tempfile.TemporaryDirectory() as temp_path:
        try:
            # Converter PDF para imagens
            logger.info("Convertendo PDF para imagens em alta resolução...")
            images = convert_from_path(
                pdf_path, 
                dpi=600,  # Alta resolução para a versão final
                output_folder=temp_path,
                fmt="png",
                thread_count=os.cpu_count(),
                use_cropbox=True
            )
            
            # Aplicar rotação selecionada
            rotated_images = []
            for i, img in enumerate(images):
                # Aplicar rotação com alta qualidade
                rotated = img.rotate(selected_angle, expand=True, resample=Image.BICUBIC)
                rotated_images.append(rotated)
            
            # Salvar imagens rotacionadas como um novo PDF
            temp_output = os.path.join(temp_path, "fixed_" + pdf_name)
            
            if rotated_images:
                logger.info("Salvando PDF com orientação corrigida...")
                rotated_images[0].save(
                    temp_output,
                    "PDF",
                    resolution=600,  # Alta resolução
                    save_all=True,
                    append_images=rotated_images[1:],
                    quality=100,  # Máxima qualidade
                    optimize=False,
                    dpi=(600, 600)
                )
                
                # Copiar metadados do PDF original
                transfer_pdf_metadata(pdf_path, temp_output)
                
                # Substituir o arquivo original
                output_path = os.path.join(OUTPUT_DIR, pdf_name)
                shutil.copy2(temp_output, output_path)
                logger.info(f"PDF corrigido salvo: {output_path}")
                return True
            
            return False
        
        except Exception as e:
            logger.error(f"Erro ao corrigir {pdf_name}: {str(e)}")
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

def open_pdf_samples(sample_paths):
    """Abre os PDFs de amostra para visualização"""
    for angle, path in sample_paths:
        try:
            logger.info(f"Abrindo amostra com rotação de {angle} graus...")
            if sys.platform.startswith('linux'):
                subprocess.Popen(['xdg-open', path], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            elif sys.platform.startswith('darwin'):
                subprocess.Popen(['open', path], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            elif sys.platform.startswith('win'):
                os.startfile(path)
        except Exception as e:
            logger.error(f"Erro ao abrir PDF: {str(e)}")

def main():
    """Função principal"""
    logger.info("Iniciando correção interativa de orientação de PDFs")
    
    # Verificar se o Poppler está instalado
    if not check_poppler_installed():
        sys.exit(1)
    
    # Criar diretório temporário
    ensure_dir(TEMP_DIR)
    
    # Processar cada documento da lista
    for doc in DOCS_TO_FIX:
        pdf_path = os.path.join(INPUT_DIR, doc)
        
        if not os.path.exists(pdf_path):
            logger.warning(f"Arquivo não encontrado: {doc}")
            continue
        
        # Criar amostras com diferentes rotações
        sample_paths = create_orientation_samples(pdf_path)
        
        if not sample_paths:
            logger.error(f"Não foi possível criar amostras para {doc}")
            continue
        
        # Abrir as amostras para visualização
        open_pdf_samples(sample_paths)
        
        # Solicitar ao usuário que escolha a orientação correta
        print(f"\nVerificando orientação para: {doc}")
        print("Abri 4 versões do documento com diferentes rotações.")
        print("Por favor, verifique qual orientação está correta e digite o número correspondente:")
        print("1. Rotação 0 graus (original)")
        print("2. Rotação 90 graus (sentido horário)")
        print("3. Rotação 180 graus (de cabeça para baixo)")
        print("4. Rotação 270 graus (sentido anti-horário)")
        
        choice = input("Sua escolha (1-4): ")
        
        # Mapear escolha para ângulo
        angle_map = {"1": 0, "2": 90, "3": 180, "4": 270}
        
        if choice in angle_map:
            selected_angle = angle_map[choice]
            # Corrigir a orientação do PDF
            if fix_orientation(pdf_path, selected_angle):
                print(f"Orientação de {doc} corrigida com rotação de {selected_angle} graus!")
            else:
                print(f"Falha ao corrigir orientação de {doc}")
        else:
            print("Escolha inválida. Pulando este documento.")
    
    print("\nProcessamento concluído!")
    print(f"Arquivos temporários disponíveis em: {TEMP_DIR}")
    print("Você pode excluir a pasta de arquivos temporários quando não precisar mais das amostras.")

if __name__ == "__main__":
    main()
