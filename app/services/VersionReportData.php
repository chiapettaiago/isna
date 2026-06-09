<?php
declare(strict_types=1);

class VersionReportData
{
    public static function version21(): array
    {
        return [
            'title' => 'ISNAPress 2.1',
            'label' => 'Relatorio de versao',
            'period' => 'Junho de 2026',
            'status' => 'Atual',
            'description' => 'Relatorio completo das mudancas lancadas em junho de 2026, com foco em seguranca dos documentos, renovacao da tela de login e reorganizacao da area logada.',
            'summaryCards' => [
                ['icon' => 'bi-shield-lock', 'label' => 'Segurança', 'value' => 'Links criptografados', 'text' => 'Documentos passam a ser abertos por tokens seguros em vez de expor diretamente o nome do arquivo.'],
                ['icon' => 'bi-door-open', 'label' => 'Acesso', 'value' => 'Login redesenhado', 'text' => 'Tela de entrada com foco em clareza, hierarquia visual e melhor experiência para administradores.'],
                ['icon' => 'bi-layout-sidebar-inset', 'label' => 'Gestão', 'value' => 'Área logada renovada', 'text' => 'Ambiente administrativo reorganizado para acesso rápido aos módulos mais usados.'],
                ['icon' => 'bi-calendar-check', 'label' => 'Lançamento', 'value' => 'Junho de 2026', 'text' => 'Entrega incremental da versão 2.1 sobre a base ISNAPress 2.0.'],
            ],
            'changeSections' => [
                [
                    'title' => 'Criptografia nos links dos documentos',
                    'icon' => 'bi-file-lock2',
                    'intro' => 'A consulta pública de títulos e documentos passou a gerar URLs com token criptografado para cada arquivo PDF.',
                    'items' => [
                        'Substituição do caminho direto dos PDFs por rotas no formato /documento/{token}.pdf.',
                        'Token gerado com AES-256-CBC, incluindo vetor de inicialização por link para evitar URLs previsíveis.',
                        'Abertura dos documentos por uma view intermediária que valida o token antes de localizar o arquivo.',
                        'Preservação da experiência de visualização: miniaturas, modal e abertura em nova aba continuam funcionando.',
                    ],
                    'result' => 'O visitante acessa o documento normalmente, mas a URL deixa de revelar o nome real do arquivo armazenado em /docs.',
                ],
                [
                    'title' => 'Novo layout da tela de login',
                    'icon' => 'bi-person-lock',
                    'intro' => 'A tela de login foi redesenhada para ficar mais limpa, objetiva e alinhada ao uso administrativo do ISNAPress.',
                    'items' => [
                        'Composição visual mais direta, com cartão de autenticação centralizado e marca institucional destacada.',
                        'Campos e botões com maior contraste, espaçamento consistente e estados de foco mais evidentes.',
                        'Tratamento visual para sessão ativa, erros de acesso e fluxo de verificação adicional.',
                        'Compatibilidade com modo escuro e com telas menores, reduzindo ruído visual no momento de entrada.',
                    ],
                    'result' => 'A autenticação ficou mais clara para usuários internos e mais consistente com o restante da experiência administrativa.',
                ],
                [
                    'title' => 'Novo layout da área logada',
                    'icon' => 'bi-window-sidebar',
                    'intro' => 'A área restrita foi reorganizada para facilitar a rotina de administração do conteúdo institucional.',
                    'items' => [
                        'Menu lateral administrativo com grupos por contexto: CMS do site, administração e sistema.',
                        'Atalhos mais visíveis para painel, páginas, posts, mídia, relatórios, usuários e informações do sistema.',
                        'Painel inicial com blocos de acesso rápido e visão de relatórios para acompanhamento da navegação.',
                        'Estrutura responsiva e suporte a tema escuro para manter a operação confortável em diferentes ambientes.',
                    ],
                    'result' => 'A equipe ganha uma área de trabalho mais previsível, com menos deslocamento entre tarefas frequentes.',
                ],
            ],
            'deliveryRows' => [
                ['area' => 'Documentos', 'before' => 'Links diretos apontavam para arquivos PDF pelo nome.', 'after' => 'Links passam a usar tokens criptografados e rota de validação.'],
                ['area' => 'Login', 'before' => 'Entrada administrativa com apresentação mais simples.', 'after' => 'Tela moderna, clara, responsiva e integrada ao visual do ISNAPress.'],
                ['area' => 'Área logada', 'before' => 'Navegação administrativa menos segmentada.', 'after' => 'Menu lateral por grupos, painel com atalhos e leitura operacional mais rápida.'],
                ['area' => 'Experiência', 'before' => 'Mudanças administrativas ficavam menos documentadas.', 'after' => 'Versão 2.1 registrada na linha do tempo e detalhada em relatório próprio.'],
            ],
            'qualityChecks' => [
                'Proteção da navegação direta para o relatório dentro da área autenticada.',
                'Compatibilidade dos links criptografados com abertura em nova aba e visualização por modal.',
                'Preservação do histórico da versão 2.0 como entrega anterior.',
                'Validação visual em layouts responsivos por regras CSS dedicadas ao relatório.',
            ],
        ];
    }
}
