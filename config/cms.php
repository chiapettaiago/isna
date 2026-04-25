<?php

declare(strict_types=1);

return [
    'pages' => [
        'home' => [
            'title' => 'Página inicial',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Bem-vindo ao Instituto Social Novo Amanhecer'],
                'hero.subtitle' => ['label' => 'Hero - subtítulo', 'type' => 'textarea', 'default' => 'Qualificando pessoas para inclusão no mercado de trabalho.'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'quem_somos.title' => ['label' => 'Quem Somos - título', 'type' => 'text', 'default' => 'Quem Somos'],
                'quem_somos.text' => ['label' => 'Quem Somos - texto', 'type' => 'textarea', 'default' => 'O ISNA é uma organização da sociedade civil de interesse público, sem fins lucrativos, que busca, através de seus projetos sociais, qualificar e requalificar pessoas para a inclusão no mercado de trabalho.'],
                'linha.title' => ['label' => 'Linha de Atuação - título', 'type' => 'text', 'default' => 'Linha de Atuação'],
                'linha.text' => ['label' => 'Linha de Atuação - texto', 'type' => 'textarea', 'default' => 'Nossos projetos sociais visam capacitar indivíduos, proporcionando-lhes as habilidades necessárias para ingressar e prosperar no mercado de trabalho.'],
                'realizacoes.title' => ['label' => 'Realizações - título', 'type' => 'text', 'default' => 'Realizações'],
                'realizacoes.subtitle' => ['label' => 'Realizações - subtítulo', 'type' => 'textarea', 'default' => 'Acompanhe, em vídeo, os destaques e conquistas do instituto a cada mês.'],
                'galeria.title' => ['label' => 'Projetos em Execução - título', 'type' => 'text', 'default' => 'Projetos em Execução'],
                'parceiros_bairro.title' => ['label' => 'Parceiros do Bairro - título', 'type' => 'text', 'default' => 'Parceiros do Bairro'],
            ],
        ],
        'quem-somos' => [
            'title' => 'Quem Somos',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Quem Somos'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'historia.title' => ['label' => 'História - título', 'type' => 'text', 'default' => 'Nossa História'],
                'historia.text' => ['label' => 'História - texto', 'type' => 'textarea', 'default' => 'O ISNA/IMPACTO SOCIAL é uma Organização de Sociedade Civil de Interesse Público de natureza filantrópica que trabalha há 13 anos a fim de contribuir na promoção da defesa dos direitos da criança, do adolescente, jovens e famílias que vivem em situação de extrema pobreza e de vulnerabilidade social no estado do Rio de Janeiro, através de projetos Educacionais, Culturais, Ambientais, Esportivos e Capacitação Profissional e Geração de Renda.'],
                'missao.title' => ['label' => 'Missão - título', 'type' => 'text', 'default' => 'Nossa Missão'],
                'missao.text' => ['label' => 'Missão - texto', 'type' => 'textarea', 'default' => 'Contribuir no processo de desenvolvimento social, ambiental, cultural, educativo, esportivo e artístico.'],
                'visao.title' => ['label' => 'Visão - título', 'type' => 'text', 'default' => 'Nossa Visão'],
                'visao.text' => ['label' => 'Visão - texto', 'type' => 'textarea', 'default' => 'Ser reconhecida como uma Organização eficiente e que seja capaz de assegurar a formação profissional do cidadão.'],
                'valores.title' => ['label' => 'Valores - título', 'type' => 'text', 'default' => 'Valores'],
                'valores.text' => ['label' => 'Valores - texto', 'type' => 'textarea', 'default' => 'Cidadania, Ética, Transparência, Integridade, Respeito à adversidade e Perseverança.'],
            ],
        ],
        'linha-atuacao' => [
            'title' => 'Linha de Atuação',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Objetivos de Desenvolvimento Sustentável (ODS)'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'intro.text' => ['label' => 'Introdução', 'type' => 'textarea', 'default' => 'Os 17 Objetivos de Desenvolvimento Sustentável (ODS) foram estabelecidos pela ONU em 2015 como uma agenda mundial para a implementação de políticas públicas voltadas ao desenvolvimento humano. É um compromisso de Estado que só será atingido com a participação ativa da sociedade.'],
            ],
        ],
        'contato' => [
            'title' => 'Contato',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Entre em Contato'],
                'hero.subtitle' => ['label' => 'Hero - subtítulo', 'type' => 'textarea', 'default' => 'Estamos prontos para ajudar e responder suas dúvidas.'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'social.title' => ['label' => 'Redes sociais - título', 'type' => 'text', 'default' => 'Siga-nos nas Redes Sociais'],
                'social.subtitle' => ['label' => 'Redes sociais - subtítulo', 'type' => 'textarea', 'default' => 'Acompanhe nossas atividades e novidades'],
                'donation.title' => ['label' => 'Chamada doação - título', 'type' => 'text', 'default' => 'Quer contribuir com nossa causa?'],
                'donation.text' => ['label' => 'Chamada doação - texto', 'type' => 'textarea', 'default' => 'Sua doação ajuda a transformar vidas através da educação e capacitação profissional.'],
            ],
        ],
        'doe' => [
            'title' => 'Doações',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Doações'],
                'hero.subtitle' => ['label' => 'Hero - subtítulo', 'type' => 'textarea', 'default' => 'Saiba como apoiar nosso instituto por meio de doações'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'main.title' => ['label' => 'Título principal', 'type' => 'text', 'default' => 'Faça sua Doação'],
                'main.text' => ['label' => 'Texto principal', 'type' => 'textarea', 'default' => 'Sua contribuição é essencial para continuarmos com nossos projetos sociais e ajudarmos mais pessoas. Selecione uma das opções abaixo para realizar sua doação de maneira segura e prática.'],
            ],
        ],
        'galeria' => [
            'title' => 'Projetos em Execução',
            'blocks' => [],
        ],
        'parceiros' => [
            'title' => 'Parceiros',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Parceiros'],
                'hero.subtitle' => ['label' => 'Hero - subtítulo', 'type' => 'textarea', 'default' => 'Conheça nossos parceiros e apoiadores que fazem nosso trabalho possível.'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'institucionais.title' => ['label' => 'Parceiros institucionais - título', 'type' => 'text', 'default' => 'Parceiros Institucionais'],
                'institucionais.subtitle' => ['label' => 'Parceiros institucionais - subtítulo', 'type' => 'textarea', 'default' => 'Organizações e empresas que apoiam nossas iniciativas'],
            ],
        ],
        'transparencia' => [
            'title' => 'Transparência',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Transparência'],
                'hero.subtitle' => ['label' => 'Hero - subtítulo', 'type' => 'textarea', 'default' => 'Conheça nossas práticas de transparência e prestação de contas.'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'intro.title' => ['label' => 'Introdução - título', 'type' => 'text', 'default' => 'Nosso Compromisso com a Transparência'],
                'intro.text' => ['label' => 'Introdução - texto', 'type' => 'textarea', 'default' => 'O ISNA acredita que a transparência é fundamental para construir confiança e demonstrar nosso compromisso com a sociedade. Aqui você encontrará informações sobre nossos projetos, recursos e resultados.'],
            ],
        ],
        'titulos-documentos' => [
            'title' => 'Títulos e Documentos',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Títulos e Documentos'],
                'hero.subtitle' => ['label' => 'Hero - subtítulo', 'type' => 'textarea', 'default' => 'Conheça nossos títulos, certificações e documentos institucionais que demonstram nosso compromisso com a transparência e a prestação de contas.'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'main.title' => ['label' => 'Título principal', 'type' => 'text', 'default' => 'Títulos e Documentos'],
            ],
        ],
        'doacoes-bancarias' => [
            'title' => 'Doações Bancárias',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Doações Bancárias'],
                'hero.subtitle' => ['label' => 'Hero - subtítulo', 'type' => 'textarea', 'default' => 'Saiba como apoiar nosso instituto por meio de doações bancárias'],
                'hero.image' => ['label' => 'Hero - imagem de fundo', 'type' => 'image', 'default' => '/images/imagem.jpg'],
                'main.title' => ['label' => 'Título principal', 'type' => 'text', 'default' => 'Doações Bancárias'],
                'main.text' => ['label' => 'Texto principal', 'type' => 'textarea', 'default' => 'Você pode contribuir por meio de depósito ou transferência Pix utilizando os dados abaixo:'],
            ],
        ],
        'mural' => [
            'title' => 'Mural Informativo',
            'blocks' => [
                'hero.title' => ['label' => 'Hero - título', 'type' => 'text', 'default' => 'Mural Informativo'],
                'hero.subtitle' => ['label' => 'Hero - subtítulo', 'type' => 'textarea', 'default' => 'Avisos e novidades do Instituto Social Novo Amanhecer'],
            ],
        ],
        'global' => [
            'title' => 'Blocos globais',
            'blocks' => [
                'contact.email' => ['label' => 'E-mail de contato', 'type' => 'text', 'default' => 'contato@isna.org.br'],
                'contact.phone' => ['label' => 'Telefone/WhatsApp', 'type' => 'text', 'default' => '(21)9 9807-4784'],
                'contact.address' => ['label' => 'Endereço', 'type' => 'textarea', 'default' => 'Rua 8 Lt 509 Qd 19, Bairro Aldeia da Prata, Itaboraí - RJ, CEP: 24858-052'],
                'instagram.url' => ['label' => 'URL do Instagram', 'type' => 'url', 'default' => 'https://instagram.com/isnasocial'],
            ],
        ],
    ],
];
