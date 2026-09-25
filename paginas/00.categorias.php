<?php

function categorias_gastos_fincontrol() {
    return [
        "Alimentação",
        "Mercado",
        "Transporte",
        "Moradia",
        "Aluguel",
        "Contas da casa",
        "Água",
        "Luz",
        "Gás",
        "Internet e telefone",
        "Saúde",
        "Farmácia",
        "Educação",
        "Vestuário",
        "Cuidados pessoais",
        "Filhos",
        "Lazer",
        "Dívidas e empréstimos",
        "Cartão de crédito",
        "Serviços",
        "Pets",
        "Emergências",
        "Outros"
    ];
}

function categorias_receitas_fincontrol() {
    return [
        "Salário",
        "Adiantamento salarial",
        "Freelance",
        "Vendas",
        "Benefícios",
        "Alimentação",
        "Investimentos",
        "Reembolso",
        "Presente",
        "Outros"
    ];
}

function categoria_fincontrol_valida($categoria, $lista) {
    return in_array($categoria, $lista, true) ? $categoria : "Outros";
}

function categoria_despesa_por_descricao_fincontrol($descricao) {
    $texto = mb_strtolower(trim($descricao), "UTF-8");

    $mapa = [
        "Dívidas e empréstimos" => ["divida", "dívida", "emprestimo", "empréstimo", "financiamento", "parcela", "acordo", "juros"],
        "Cartão de crédito" => ["cartao", "cartão", "credito", "crédito", "fatura", "itau", "itaú"],
        "Alimentação" => ["alimentacao", "alimentação", "restaurante", "lanche", "padaria", "ifood", "delivery", "comida", "pizza"],
        "Mercado" => ["mercado", "supermercado", "hortifruti", "acougue", "açougue", "mercearia", "atacadao", "atacadão", "carrefour", "extra"],
        "Transporte" => ["uber", "99", "onibus", "ônibus", "metro", "metrô", "combustivel", "combustível", "gasolina", "estacionamento", "pedagio", "pedágio", "transporte"],
        "Moradia" => ["condominio", "condomínio", "casa", "moradia", "iptu", "manutencao casa", "manutenção casa", "leroy", "leroa"],
        "Aluguel" => ["aluguel"],
        "Contas da casa" => ["conta da casa", "contas da casa", "condominio", "condomínio"],
        "Água" => ["agua", "água", "saneamento", "sabesp"],
        "Luz" => ["luz", "energia", "enel", "eletricidade"],
        "Gás" => ["gas", "gás", "botijao", "botijão"],
        "Internet e telefone" => ["internet", "telefone", "celular", "vivo", "claro", "fibra", "banda larga"],
        "Saúde" => ["saude", "saúde", "medico", "médico", "consulta", "exame", "plano de saude", "plano de saúde", "dentista"],
        "Farmácia" => ["farmacia", "farmácia", "remedio", "remédio", "drogaria"],
        "Educação" => ["educacao", "educação", "curso", "faculdade", "escola", "livro", "material escolar"],
        "Vestuário" => ["roupa", "vestuario", "vestuário", "calcado", "calçado", "sapato", "netshoes"],
        "Cuidados pessoais" => ["barbearia", "cabeleireiro", "salao", "salão", "cosmetico", "cosmético", "higiene"],
        "Filhos" => ["filho", "filha", "crianca", "criança", "creche"],
        "Lazer" => ["lazer", "cinema", "show", "viagem", "hotel", "passeio", "netflix", "spotify"],
        "Serviços" => ["servico", "serviço", "assinatura", "youtube", "google", "apple", "microsoft", "software"],
        "Pets" => ["pet", "veterinario", "veterinário", "racao", "ração"],
        "Emergências" => ["emergencia", "emergência", "imprevisto", "conserto urgente"]
    ];

    foreach ($mapa as $categoria => $palavras) {
        foreach ($palavras as $palavra) {
            if (str_contains($texto, $palavra)) {
                return $categoria;
            }
        }
    }

    return "Outros";
}

function garantir_coluna_categoria_fincontrol($conexao, $tabela) {
    if (!$conexao || !in_array($tabela, ["receitas", "despesas"], true)) {
        return false;
    }

    $resultado = $conexao->query("SHOW COLUMNS FROM `$tabela` LIKE 'categoria'");

    if ($resultado && $resultado->num_rows > 0) {
        return true;
    }

    return (bool) $conexao->query("ALTER TABLE `$tabela` ADD `categoria` varchar(100) NOT NULL DEFAULT 'Outros' AFTER `descricao`");
}

function coluna_categoria_existe_fincontrol($conexao, $tabela) {
    if (!$conexao || !in_array($tabela, ["receitas", "despesas"], true)) {
        return false;
    }

    $resultado = $conexao->query("SHOW COLUMNS FROM `$tabela` LIKE 'categoria'");
    return $resultado && $resultado->num_rows > 0;
}
