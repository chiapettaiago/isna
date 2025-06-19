#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para otimizar arquivos PDF:
- Corrigir a orientação
- Melhorar a nitidez 
- Aumentar a resolução
- Preservar o nome original

Autor: GitHub Copilot
Data: 18 de Junho de 2025
"""

import os
import tempfile
import shutil
import subprocess
import sys
import argparse
from pathlib import Path
import PyPDF2
from pdf2image import convert_from_path
from PIL import Image, ImageEnhance, ImageFilter
import logging
import platform

# Configuração de logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("pdf_optimization.log"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger()

# Configurações
INPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "docs")
OUTPUT_DIR = INPUT_DIR  # Mesmo diretório de entrada
TEMP_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "temp_pdfs")
DPI = 600  # Resolução aumentada
QUALITY = 100  # Qualidade máxima
ENHANCEMENT_FACTOR = 1.2  # Fator de aumento de nitidez reduzido para evitar distorção
AUTO_ROTATE = True  # Ativar/desativar correção automática de orientação
CONFIDENCE_THRESHOLD = 1.2  # Rotaciona apenas se o score for 20% melhor que o original
PRESERVE_ASPECT_RATIO = True  # Garantir que a proporção seja preservada

# Lista de documentos para rotacionar 180 graus (inverter orientação)
ROTATE_180_DOCS = [
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
    """Verifica se o Poppler está instalado e disponível no PATH"""
    try:
        if platform.system() == "Windows":
            # No Windows, verificamos se existe o diretório poppler no PATH
            from shutil import which
            poppler_path = which("pdftoppm")
            return poppler_path is not None
        else:
            # No Linux/Mac, verificamos se o comando está disponível
            subprocess.run(
                ["pdftoppm", "-v"], 
                stdout=subprocess.PIPE, 
                stderr=subprocess.PIPE, 
                check=True
            )
        return True
    except (subprocess.SubprocessError, FileNotFoundError):
        return False

def install_poppler_guide():
    """Exibe guia para instalação do Poppler"""
    system = platform.system()
    logger.error("ERRO: Poppler não encontrado no sistema!")
    logger.error("O Poppler é necessário para processar os PDFs.")
    logger.error("\nGuia de instalação do Poppler:")
    
    if system == "Linux":
        if os.path.exists("/etc/debian_version"):
            # Debian/Ubuntu
            logger.error("  Para Debian/Ubuntu, execute:")
            logger.error("  sudo apt-get update && sudo apt-get install -y poppler-utils")
        elif os.path.exists("/etc/redhat-release"):
            # CentOS/RHEL/Fedora
            logger.error("  Para CentOS/RHEL/Fedora, execute:")
            logger.error("  sudo yum install -y poppler-utils")
        else:
            # Genérico para outras distribuições
            logger.error("  Para sua distribuição Linux, instale o pacote 'poppler-utils'")
    elif system == "Darwin":  # macOS
        logger.error("  Para macOS, execute:")
        logger.error("  brew install poppler")
    elif system == "Windows":
        logger.error("  Para Windows:")
        logger.error("  1. Baixe os binários do Poppler em: https://github.com/oschwartz10612/poppler-windows/releases/")
        logger.error("  2. Extraia o arquivo e coloque a pasta 'poppler-xx' em um local conhecido")
        logger.error("  3. Adicione o caminho completo para a pasta 'bin' nos binários do Poppler ao PATH do sistema")
        logger.error("  4. Reinicie o prompt de comando/terminal após ajustar o PATH")
    
    logger.error("\nApós instalar o Poppler, execute este script novamente.")

def detect_orientation(image):
    """
    Detecta a orientação correta da imagem e rotaciona apenas se necessário
    Algoritmo simplificado: assume que o texto deve estar na horizontal
    """
    # Esta é uma implementação simplificada
    # Em um cenário real, usaríamos OCR ou machine learning para detectar orientação
    
    # Verificamos os 4 ângulos possíveis e escolhemos o melhor
    scores = []
    for angle in [0, 90, 180, 270]:
        rotated = image.rotate(angle, expand=True)
        # Análise de gradientes horizontais vs verticais (heurística simples)
        horiz_var = measure_horizontal_variance(rotated)
        scores.append((angle, horiz_var))
    
    # O ângulo com maior variância horizontal é provavelmente o correto
    best_angle = max(scores, key=lambda x: x[1])[0]
    
    # Definimos um limiar de confiança - se a diferença for pequena, mantemos a orientação original
    # Comparamos o score do melhor ângulo com o score da orientação original (0 graus)
    original_score = next(score for angle, score in scores if angle == 0)
    best_score = next(score for angle, score in scores if angle == best_angle)
    
    # Se o melhor ângulo não for 0, mas a diferença de score for pequena (menos de 10%),
    # preferimos manter a orientação original para evitar rotações desnecessárias
    confidence_threshold = 1.1  # 10% de diferença
    
    if best_angle != 0:
        if best_score > original_score * confidence_threshold:
            logger.info(f"Rotacionando imagem em {best_angle} graus (confiança: {best_score/original_score:.2f}x)")
            return image.rotate(best_angle, expand=True)
        else:
            logger.info(f"Mantendo orientação original (diferença insuficiente: {best_score/original_score:.2f}x)")
    
    return image

def measure_horizontal_variance(image):
    """
    Medição aprimorada da variância horizontal e detecção de orientação de texto
    Esta implementação considera tanto a variância horizontal quanto vertical
    e compara os padrões para determinar a orientação mais provável do texto
    """
    # Convertemos para escala de cinza para análise
    if image.mode != 'L':
        gray = image.convert('L')
    else:
        gray = image
        
    width, height = gray.size
    
    # Amostramos mais linhas para melhor precisão
    sample_lines_h = min(30, height // 10)  # Mais amostras, mas não excessivas
    sample_lines_v = min(30, width // 10)
    
    # Calculamos tanto a variância horizontal quanto vertical
    h_variance = calculate_directional_variance(gray, sample_lines_h, width, height, is_horizontal=True)
    v_variance = calculate_directional_variance(gray, sample_lines_v, width, height, is_horizontal=False)
    
    # Para documentos de texto, geralmente a variância horizontal é maior
    # quando o texto está na orientação correta (devido às linhas de texto)
    # Retornamos uma pontuação que favorece isso
    return h_variance * 1.2 - v_variance * 0.5  # Ponderação para favorecer linhas horizontais de texto

def calculate_directional_variance(img, sample_lines, width, height, is_horizontal=True):
    """
    Calcula a variância em uma direção específica (horizontal ou vertical)
    usando técnicas de detecção de bordas para melhor identificar texto
    """
    total_variance = 0
    edges = img.filter(ImageFilter.FIND_EDGES)  # Detectar bordas ajuda a identificar linhas de texto
    
    # Para cada linha de amostra
    for i in range(1, sample_lines + 1):
        if is_horizontal:
            # Linha horizontal
            y = int(height * i / (sample_lines + 1))
            line = [edges.getpixel((x, y)) for x in range(0, width, max(1, width // 200))]
        else:
            # Linha vertical
            x = int(width * i / (sample_lines + 1))
            line = [edges.getpixel((x, y)) for y in range(0, height, max(1, height // 200))]
            
        if line and len(line) > 1:  # Evitar divisão por zero ou listas pequenas demais
            # Variância + detecção de transições (mudanças de claro para escuro indicam texto)
            line_mean = sum(line) / len(line)
            line_variance = sum((x - line_mean)**2 for x in line) / len(line)
            
            # Contagem de transições (quanto mais transições, mais provável haver texto)
            transitions = sum(1 for i in range(1, len(line)) if abs(line[i] - line[i-1]) > 30)
            
            # Combinamos variância e transições para pontuação final
            score = line_variance * 0.7 + transitions * 0.3
            total_variance += score
    
    return total_variance / sample_lines if sample_lines > 0 else 0

def enhance_image(image):
    """Melhora a nitidez e qualidade da imagem"""
    # Aplicar filtro de nitidez
    enhanced = ImageEnhance.Sharpness(image).enhance(ENHANCEMENT_FACTOR)
    
    # Melhorar o contraste
    enhanced = ImageEnhance.Contrast(enhanced).enhance(1.2)
    
    # Ajuste de brilho
    enhanced = ImageEnhance.Brightness(enhanced).enhance(1.05)
    
    # Filtro de redução de ruído
    enhanced = enhanced.filter(ImageFilter.UnsharpMask(radius=2, percent=150, threshold=3))
    
    return enhanced

def process_pdf(pdf_path):
    """Processa um arquivo PDF, melhorando-o e salvando com o mesmo nome"""
    pdf_name = os.path.basename(pdf_path)
    logger.info(f"Processando: {pdf_name}")
    
    # Criar diretório temporário único para este arquivo
    with tempfile.TemporaryDirectory() as temp_path:
        try:
            # Converter PDF para imagens em alta qualidade
            logger.info("Convertendo PDF para imagens em alta resolução...")
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
            
            # Processar cada página
            enhanced_images = []
            for i, img in enumerate(images):
                logger.info(f"Processando página {i+1}/{len(images)}")
                
                # Verificar se o documento está na lista de rotação forçada
                if pdf_name in ROTATE_180_DOCS:
                    logger.info(f"Aplicando rotação forçada de 180 graus em {pdf_name}")
                    # Rotação de 180 graus para inverter a orientação
                    oriented_img = img.rotate(180, expand=True)
                # Caso contrário, aplicar detecção automática se habilitada
                elif AUTO_ROTATE:
                    oriented_img = detect_orientation(img)
                else:
                    logger.info("Correção automática de orientação desativada")
                    oriented_img = img
                    
                # Melhorar nitidez e qualidade
                enhanced_img = enhance_image(oriented_img)
                enhanced_images.append(enhanced_img)
                
            # Salvar imagens otimizadas como um novo PDF
            temp_output = os.path.join(temp_path, "enhanced_" + pdf_name)
            if enhanced_images:
                logger.info("Salvando imagens otimizadas como PDF...")
                # Salvar em PDF com configurações otimizadas para preservar qualidade
                enhanced_images[0].save(
                    temp_output,
                    "PDF",
                    resolution=DPI,
                    save_all=True,
                    append_images=enhanced_images[1:],
                    quality=QUALITY,
                    optimize=False,  # Desativar otimização automática para preservar qualidade
                    dpi=(DPI, DPI)  # Especificar DPI para X e Y para manter proporção
                )
                
                # Copiar metadados do PDF original para o novo
                transfer_pdf_metadata(pdf_path, temp_output)
                
                # Substituir o arquivo original pelo otimizado
                output_path = os.path.join(OUTPUT_DIR, pdf_name)
                shutil.copy2(temp_output, output_path)
                logger.info(f"PDF otimizado salvo: {output_path}")
            else:
                logger.warning(f"Nenhuma imagem gerada para {pdf_name}")
                
        except Exception as e:
            error_msg = str(e)
            logger.error(f"Erro ao processar {pdf_name}: {error_msg}")
            if "Unable to get page count" in error_msg and "poppler" in error_msg:
                logger.error("ERRO: Problema com a instalação do Poppler detectado.")
                install_poppler_guide()
                sys.exit(1)

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

def parse_arguments():
    """Processa os argumentos da linha de comando"""
    parser = argparse.ArgumentParser(
        description="Otimiza PDFs melhorando a nitidez, resolução e orientação"
    )
    parser.add_argument(
        "--no-rotate", 
        action="store_true", 
        help="Desativa a correção automática de orientação"
    )
    parser.add_argument(
        "--files", 
        nargs="+", 
        help="Arquivos específicos a serem processados (apenas nomes de arquivo, não caminhos completos)"
    )
    parser.add_argument(
        "--dpi", 
        type=int, 
        default=DPI, 
        help=f"Resolução desejada em DPI (padrão: {DPI})"
    )
    parser.add_argument(
        "--input-dir", 
        default=INPUT_DIR, 
        help=f"Diretório de entrada (padrão: {INPUT_DIR})"
    )
    return parser.parse_args()

def main():
    """Função principal"""
    # Processar argumentos da linha de comando
    args = parse_arguments()
    
    # Aplicar configurações a partir dos argumentos
    global AUTO_ROTATE, DPI, INPUT_DIR
    AUTO_ROTATE = not args.no_rotate
    DPI = args.dpi
    
    if args.input_dir:
        INPUT_DIR = args.input_dir
    
    logger.info(f"Iniciando o processamento de PDFs (Rotação automática: {'ATIVADA' if AUTO_ROTATE else 'DESATIVADA'})")
    
    # Verificar se o Poppler está instalado
    if not check_poppler_installed():
        install_poppler_guide()
        sys.exit(1)
    
    # Garantir que os diretórios existam
    ensure_dir(INPUT_DIR)
    ensure_dir(OUTPUT_DIR)
    
    # Listar todos os PDFs no diretório de entrada, ou usar os especificados
    if args.files:
        pdf_files = [f for f in args.files if f.lower().endswith('.pdf') and os.path.exists(os.path.join(INPUT_DIR, f))]
        if not pdf_files:
            logger.warning(f"Nenhum dos arquivos especificados foi encontrado em {INPUT_DIR}")
            return
    else:
        pdf_files = [f for f in os.listdir(INPUT_DIR) if f.lower().endswith('.pdf')]
    
    total_files = len(pdf_files)
    
    if total_files == 0:
        logger.warning(f"Nenhum arquivo PDF encontrado em {INPUT_DIR}")
        return
    
    logger.info(f"Encontrados {total_files} arquivos PDF para processar")
    
    # Processar cada arquivo PDF
    for i, pdf_file in enumerate(pdf_files):
        logger.info(f"Processando arquivo {i+1}/{total_files}: {pdf_file}")
        pdf_path = os.path.join(INPUT_DIR, pdf_file)
        process_pdf(pdf_path)
    
    logger.info("Processamento de PDFs concluído!")

if __name__ == "__main__":
    main()
