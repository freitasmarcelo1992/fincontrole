<?php
require_once "00.sessao.php";
require_once "00.noticias.php";

$noticiasHome = fincontrol_noticias_home(4);
$_SESSION['noticias_csrf'] = $_SESSION['noticias_csrf'] ?? bin2hex(random_bytes(24));
$noticiaPrincipal = $noticiasHome[0] ?? ['titulo' => 'Notícias em atualização', 'resumo' => 'Aguardando notícias verificadas nas fontes.', 'fonte' => 'FinControle', 'imagem' => 'img/news-planning.jpg', 'link' => '01.home.php'];
$noticiasSecundarias = array_slice($noticiasHome, 1, 3);
$hojeHome = new DateTimeImmutable('today');
$fimMesHome = $hojeHome->modify('last day of this month');
$diasRestantesMes = (int) $hojeHome->diff($fimMesHome)->format('%a');
$mesesHome = [
    1 => 'janeiro',
    2 => 'fevereiro',
    3 => 'março',
    4 => 'abril',
    5 => 'maio',
    6 => 'junho',
    7 => 'julho',
    8 => 'agosto',
    9 => 'setembro',
    10 => 'outubro',
    11 => 'novembro',
    12 => 'dezembro',
];
$mesAtualHome = $mesesHome[(int) $hojeHome->format('n')] ?? $hojeHome->format('m');
$diasRestantesTexto = $diasRestantesMes === 1 ? 'Ainda falta 1 dia' : 'Ainda faltam ' . $diasRestantesMes . ' dias';

function fincontrol_home_h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function fincontrol_home_news_link(array $noticia): string
{
    if (function_exists('fincontrol_noticias_link_destino_valido') && fincontrol_noticias_link_destino_valido($noticia)) {
        return (string) $noticia['link'];
    }

    return '01.home.php#noticias';
}

function fincontrol_home_news_target(array $noticia): string
{
    return (function_exists('fincontrol_noticias_link_destino_valido') && fincontrol_noticias_link_destino_valido($noticia)) ? ' target="_blank" rel="noopener noreferrer"' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once "00.pwa.php"; ?>
    <?php if (file_exists("00.google_tag.php")) { require_once "00.google_tag.php"; } ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FinControle organiza finanças, treinos, medidas, relatórios e notícias úteis em uma rotina simples.">
    <title>FinControle - Finanças, treinos e rotina em um só app</title>
    <link href="img/logo-FinControle.png" rel="icon" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #061826;
            --bg-2: #082338;
            --card: #0d2a42;
            --card-2: #102f49;
            --line: rgba(87, 190, 255, .22);
            --blue: #0087c7;
            --cyan: #00b7e8;
            --text: #ffffff;
            --muted: #a9bdd0;
            --green: #24e56b;
            --red: #ff5252;
            --amber: #ffb020;
        }

        * {
            box-sizing: border-box;
            letter-spacing: 0;
        }

        body {
            margin: 0;
            font-family: Poppins, Arial, sans-serif;
            background:
                radial-gradient(circle at 82% 2%, rgba(0, 183, 232, .18), transparent 34%),
                linear-gradient(180deg, #020b14 0%, var(--bg) 52%, #020b14 100%);
            color: var(--text);
            overflow-x: hidden;
            width: 100%;
        }

        html {
            overflow-x: hidden;
            width: 100%;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .fc-page {
            width: min(100%, 480px);
            min-height: 100vh;
            margin: 0 auto;
            padding: 24px 18px 92px;
        }

        .fc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .fc-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            font-size: 1.45rem;
            line-height: 1;
        }

        .fc-brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--cyan), var(--blue));
            box-shadow: 0 14px 28px rgba(0, 183, 232, .22);
        }

        .fc-brand span span {
            color: var(--cyan);
        }

        .fc-header-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }

        .fc-login,
        .fc-install {
            border: 1px solid var(--cyan);
            border-radius: 14px;
            padding: 12px 22px;
            font-weight: 800;
            background: rgba(0, 183, 232, .05);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .03);
        }

        .fc-install {
            color: #fff;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .fc-install[hidden] {
            display: none !important;
        }

        .fc-card {
            border: 1px solid var(--line);
            border-radius: 20px;
            background: linear-gradient(145deg, rgba(16, 47, 73, .96), rgba(7, 29, 47, .94));
            box-shadow: 0 18px 42px rgba(0, 0, 0, .24);
        }

        a.fc-card {
            color: inherit;
            text-decoration: none;
        }

        .news-card {
            display: block;
            position: relative;
            min-height: 320px;
            padding: 26px;
            overflow: hidden;
        }

        .news-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(7, 29, 47, .96) 0%, rgba(7, 29, 47, .80) 56%, rgba(7, 29, 47, .46) 100%),
                radial-gradient(circle at 80% 38%, rgba(0, 183, 232, .18), transparent 28%),
                linear-gradient(120deg, transparent 0%, rgba(0, 183, 232, .08) 100%);
            pointer-events: none;
            z-index: 1;
        }

        .news-main-img {
            position: absolute;
            inset: 0;
            z-index: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: .58;
            filter: saturate(1.05) contrast(1.08);
        }

        .news-visual {
            position: absolute;
            right: 18px;
            top: 56px;
            width: 150px;
            height: 150px;
            opacity: .82;
            color: var(--cyan);
        }

        .news-content {
            position: relative;
            z-index: 2;
            max-width: 75%;
        }

        .source-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--line);
            border-radius: 13px;
            padding: 10px 13px;
            color: #d6edf9;
            background: rgba(8, 35, 56, .72);
            font-weight: 600;
            font-size: .85rem;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 2.05rem;
            line-height: 1.13;
            margin: 0 0 16px;
            font-weight: 900;
            overflow-wrap: anywhere;
        }

        .news-card p {
            color: #d5e1ec;
            font-size: 1rem;
            line-height: 1.45;
            margin: 0;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }

        .app-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(150px, .95fr);
            align-items: center;
            gap: 16px;
            min-height: 360px;
            padding: 24px;
        }

        .app-hero::before {
            background:
                linear-gradient(90deg, rgba(6, 24, 38, .98) 0%, rgba(6, 24, 38, .9) 48%, rgba(6, 24, 38, .35) 100%),
                radial-gradient(circle at 84% 18%, rgba(0, 183, 232, .28), transparent 34%),
                linear-gradient(145deg, rgba(16, 47, 73, .98), rgba(6, 24, 38, .96));
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            border: 1px solid var(--line);
            border-radius: 13px;
            padding: 9px 12px;
            color: var(--cyan);
            background: rgba(0, 183, 232, .07);
            font-size: .78rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 6px;
        }

        .hero-snapshot {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-top: 2px;
        }

        .hero-snapshot span {
            display: grid;
            gap: 2px;
            min-height: 58px;
            padding: 10px;
            border: 1px solid rgba(87, 190, 255, .2);
            border-radius: 14px;
            background: rgba(2, 11, 20, .36);
        }

        .hero-snapshot strong {
            color: #fff;
            font-size: .9rem;
            line-height: 1.05;
        }

        .hero-snapshot small {
            color: var(--muted);
            font-size: .62rem;
            line-height: 1.15;
            font-weight: 700;
        }

        .hero-showcase {
            position: relative;
            z-index: 2;
            min-height: 250px;
            display: grid;
            place-items: center;
        }

        .phone-frame {
            width: min(190px, 100%);
            aspect-ratio: 9 / 16;
            padding: 8px;
            border-radius: 28px;
            background: linear-gradient(180deg, rgba(255,255,255,.22), rgba(255,255,255,.05));
            border: 1px solid rgba(255,255,255,.26);
            box-shadow: 0 24px 60px rgba(0,0,0,.38);
            transform: rotate(4deg);
        }

        .phone-frame img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: top center;
            border-radius: 21px;
        }

        .hero-mini-card {
            position: absolute;
            left: 0;
            bottom: 18px;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 160px;
            padding: 10px 11px;
            border-radius: 15px;
            background: rgba(8, 35, 56, .9);
            border: 1px solid rgba(0, 183, 232, .32);
            box-shadow: 0 16px 34px rgba(0,0,0,.28);
            color: #fff;
            font-size: .7rem;
            font-weight: 800;
            line-height: 1.15;
        }

        .hero-mini-card i {
            color: var(--green);
        }

        .hero-points {
            display: none;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .hero-points span {
            display: grid;
            gap: 4px;
            min-height: 72px;
            padding: 10px;
            border: 1px solid rgba(87, 190, 255, .18);
            border-radius: 14px;
            background: rgba(0, 183, 232, .05);
            color: #d8edf8;
            font-size: .72rem;
            line-height: 1.18;
            font-weight: 700;
        }

        .hero-points i {
            color: var(--cyan);
            font-size: 1rem;
        }

        .carousel-meta {
            position: absolute;
            z-index: 2;
            left: 26px;
            right: 26px;
            bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #c8d7e4;
            font-weight: 600;
        }

        .dots {
            display: inline-flex;
            gap: 9px;
        }

        .dots i {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .28);
            cursor: pointer;
        }

        .dots i.active {
            background: var(--cyan);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 26px 0 14px;
            font-size: 1.35rem;
            font-weight: 900;
        }

        .section-title i {
            color: var(--cyan);
        }

        .news-list {
            display: grid;
            gap: 12px;
        }

        .news-item {
            display: grid;
            grid-template-columns: 112px 1fr 18px;
            gap: 14px;
            align-items: center;
            padding: 12px;
        }

        .news-thumb {
            position: relative;
            height: 82px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--cyan);
            font-size: 2rem;
            background:
                radial-gradient(circle at 72% 24%, rgba(0, 183, 232, .32), transparent 36%),
                linear-gradient(135deg, rgba(0, 135, 199, .32), rgba(7, 29, 47, .98));
            border: 1px solid rgba(0, 183, 232, .35);
        }

        .news-thumb::before,
        .news-thumb::after {
            content: "";
            position: absolute;
            pointer-events: none;
        }

        .news-thumb i {
            position: relative;
            z-index: 2;
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 183, 232, .16);
            box-shadow: 0 0 24px rgba(0, 183, 232, .28);
        }

        .news-thumb.has-img {
            padding: 0;
            background: rgba(8, 35, 56, .96);
        }

        .news-thumb.has-img::before,
        .news-thumb.has-img::after {
            display: none;
        }

        .news-thumb.has-img img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .thumb-market::before {
            inset: 14px 12px;
            border-radius: 999px;
            border: 1px solid rgba(0, 183, 232, .28);
        }

        .thumb-market::after {
            left: 14px;
            right: 12px;
            bottom: 18px;
            height: 24px;
            border-left: 3px solid rgba(36, 229, 107, .75);
            border-bottom: 3px solid rgba(36, 229, 107, .75);
            transform: skewX(-24deg);
            opacity: .75;
        }

        .thumb-card::before {
            width: 58px;
            height: 36px;
            border-radius: 8px;
            border: 2px solid rgba(0, 183, 232, .6);
            background: rgba(255, 255, 255, .05);
        }

        .thumb-card::after {
            width: 36px;
            height: 3px;
            top: 31px;
            left: 25px;
            border-radius: 999px;
            background: rgba(0, 183, 232, .62);
        }

        .thumb-plan::before {
            left: 18px;
            right: 18px;
            bottom: 22px;
            height: 28px;
            border-left: 3px solid rgba(0, 183, 232, .65);
            border-bottom: 3px solid rgba(0, 183, 232, .65);
            transform: skewX(-18deg);
        }

        .thumb-plan::after {
            right: 18px;
            top: 16px;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: rgba(36, 229, 107, .18);
            border: 1px solid rgba(36, 229, 107, .45);
        }

        .news-item h3 {
            margin: 0 0 7px;
            font-size: 1rem;
            line-height: 1.24;
        }

        .news-item strong {
            display: block;
            color: var(--cyan);
            font-size: .82rem;
            margin-bottom: 4px;
        }

        .news-item small {
            color: var(--muted);
            font-weight: 600;
        }

        .impact-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
            border-color: rgba(255, 176, 32, .45);
            background: linear-gradient(120deg, rgba(96, 65, 7, .46), rgba(13, 42, 66, .9));
        }

        .impact-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .impact-icon {
            width: 58px;
            height: 58px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--amber);
            border: 1px solid rgba(255, 176, 32, .55);
            background: rgba(255, 176, 32, .12);
            font-size: 1.6rem;
        }

        .impact-card h3,
        .planning-card h3,
        .cta-card h3 {
            margin: 0 0 6px;
            font-size: 1.2rem;
            font-weight: 900;
        }

        .impact-card p,
        .planning-card p,
        .cta-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.45;
        }

        .planning-card {
            padding: 20px;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 0;
        }

        .planning-kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--cyan);
            font-size: .78rem;
            font-weight: 900;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .step {
            display: grid;
            grid-template-columns: 36px 1fr 14px;
            align-items: center;
            gap: 10px;
            min-height: 86px;
            padding: 12px;
            border: 1px solid rgba(87, 190, 255, .18);
            border-radius: 14px;
            background: rgba(0, 183, 232, .05);
        }

        .step:last-child {
            border-bottom: 1px solid rgba(87, 190, 255, .18);
        }

        .step-number {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--blue), var(--cyan));
            font-weight: 900;
        }

        .step-copy {
            display: grid;
            gap: 2px;
        }

        .step-copy strong {
            font-size: .86rem;
            line-height: 1.15;
        }

        .step-copy small {
            color: var(--muted);
            font-size: .68rem;
            line-height: 1.25;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .feature-card {
            min-height: 118px;
            padding: 14px;
            border: 1px solid rgba(87, 190, 255, .18);
            border-radius: 16px;
            background: rgba(0, 183, 232, .05);
        }

        .feature-card i {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            color: #fff;
            background: linear-gradient(135deg, var(--blue), var(--cyan));
        }

        .feature-card strong {
            display: block;
            margin-bottom: 5px;
            font-size: .94rem;
            line-height: 1.12;
        }

        .feature-card small {
            display: block;
            color: var(--muted);
            font-size: .72rem;
            line-height: 1.28;
        }

        .guide-main {
            display: grid;
            grid-template-columns: 94px 1fr;
            gap: 12px;
            align-items: center;
            margin-top: 14px;
            padding: 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .guide-thumb {
            position: relative;
            width: 94px;
            height: 70px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(0, 183, 232, .28);
            background: rgba(0, 183, 232, .08);
        }

        .guide-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .guide-thumb small {
            position: absolute;
            top: 6px;
            right: 6px;
            padding: 3px 7px;
            border-radius: 999px;
            background: var(--cyan);
            color: #fff;
            font-size: .65rem;
            font-weight: 900;
        }

        .guide-copy {
            min-width: 0;
        }

        .guide-main small,
        .guide-links a {
            color: var(--cyan);
            font-weight: 800;
        }

        .guide-links {
            display: grid;
            gap: 0;
            margin-top: 14px;
        }

        .guide-links a {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 2px;
            border-top: 1px solid rgba(255, 255, 255, .1);
            text-decoration: none;
        }

        .month-moment {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            align-items: center;
            margin-top: 20px;
            padding: 20px;
            border-color: rgba(0, 183, 232, .45);
            background:
                radial-gradient(circle at top right, rgba(0, 183, 232, .2), transparent 34%),
                linear-gradient(180deg, rgba(16, 47, 73, .98), rgba(8, 35, 56, .98));
        }

        .month-days {
            display: grid;
            place-items: center;
            min-height: 126px;
            border-radius: 16px;
            color: #0b2a42;
            background: linear-gradient(180deg, #ffffff, #d9f2ff);
            box-shadow: inset 0 0 0 1px rgba(0, 183, 232, .18);
        }

        .month-days span {
            color: var(--blue);
            font-size: .8rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .month-days strong {
            color: var(--blue);
            font-size: 3.2rem;
            line-height: .9;
        }

        .month-copy {
            display: grid;
            gap: 9px;
        }

        .month-moment strong {
            color: var(--cyan);
            font-size: .78rem;
            text-transform: uppercase;
        }

        .month-moment h3,
        .discovery-card h3 {
            margin: 0;
            font-size: 1.35rem;
            line-height: 1.2;
            font-weight: 900;
        }

        .month-moment p,
        .discovery-card p {
            color: var(--muted);
            margin: 0;
            line-height: 1.48;
        }

        .month-benefits {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-top: 2px;
        }

        .month-benefits span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #d8edf8;
            font-size: .68rem;
            line-height: 1.15;
        }

        .month-benefits i {
            color: var(--green);
        }

        .discovery-card {
            display: grid;
            grid-template-columns: 48px 1fr auto;
            gap: 14px;
            align-items: center;
            margin-top: 20px;
            padding: 20px;
        }

        .discovery-card .fc-brand-mark {
            width: 48px;
            height: 48px;
        }

        .discovery-list {
            display: grid;
            gap: 8px;
            color: var(--muted);
            font-size: .95rem;
        }

        .discovery-list span {
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }

        .discovery-list i {
            color: var(--green);
            margin-top: 3px;
        }

        .low-friction {
            color: var(--muted);
            font-size: .8rem;
            font-style: italic;
            text-align: center;
        }

        .cta-card {
            display: grid;
            grid-template-columns: 78px 1fr;
            gap: 18px;
            align-items: center;
            margin-top: 20px;
            padding: 20px;
            border-color: rgba(0, 183, 232, .58);
        }

        .cta-icon {
            width: 74px;
            height: 74px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #073652, #00b7e8);
            font-size: 2rem;
            box-shadow: 0 16px 34px rgba(0, 183, 232, .18);
        }

        .cta-actions {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            border-radius: 14px;
            font-weight: 900;
            border: 1px solid var(--cyan);
        }

        .btn-primary {
            border: 0;
            background: linear-gradient(135deg, var(--cyan), var(--blue));
            color: #fff;
        }

        .btn-secondary {
            background: rgba(0, 183, 232, .04);
            color: #fff;
        }

        .bottom-nav {
            position: fixed;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: min(100%, 480px);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            background: rgba(3, 17, 29, .96);
            border-top: 1px solid rgba(87, 190, 255, .18);
            backdrop-filter: blur(14px);
            z-index: 10;
        }

        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #9fb3c4;
            font-size: .76rem;
            font-weight: 700;
        }

        .bottom-nav a.active {
            color: var(--cyan);
        }

        .bottom-nav i {
            font-size: 1.18rem;
        }

        @media (min-width: 900px) {
            .fc-page {
                width: min(1240px, calc(100% - 64px));
                padding: 34px 0 56px;
            }

            .fc-header,
            .news-card,
            .section-title,
            .news-list,
            .impact-card,
            .planning-card,
            .month-moment,
            .discovery-card,
            .cta-card {
                width: 100%;
                max-width: none;
                margin-left: auto;
                margin-right: auto;
            }

            .news-card {
                min-height: 360px;
            }

            .app-hero {
                min-height: 390px;
                align-content: center;
            }

            .news-visual {
                width: 260px;
                height: 260px;
            }

            .news-content {
                max-width: 58%;
            }

            .news-list {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .news-item {
                min-height: 128px;
                grid-template-columns: 96px 1fr 16px;
            }

            .news-item h3 {
                font-size: .92rem;
            }

            .steps {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .feature-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }

            .step {
                border-bottom: 1px solid rgba(87, 190, 255, .18);
                border-right: 1px solid rgba(87, 190, 255, .18);
                padding: 12px;
            }

            .step:last-child {
                border-right: 1px solid rgba(87, 190, 255, .18);
                padding-right: 12px;
            }

            .guide-main {
                grid-template-columns: 112px minmax(0, 1fr);
                align-items: center;
            }

            .guide-main h3,
            .guide-main p {
                margin: 0;
            }

            .guide-main .btn {
                grid-column: auto;
                grid-row: auto;
            }

            .guide-links {
                grid-template-columns: 1fr;
            }

            .month-moment {
                grid-template-columns: 132px minmax(0, 1fr);
                align-items: center;
            }

            .month-moment .btn {
                grid-column: auto;
                grid-row: auto;
            }

            .discovery-card {
                grid-template-columns: 56px minmax(0, 1fr) 220px;
                align-items: center;
            }

            .discovery-card .discovery-list,
            .discovery-card .btn,
            .discovery-card .low-friction {
                grid-column: auto;
            }

            .cta-actions {
                grid-template-columns: 1fr 1fr;
            }

            .bottom-nav {
                display: none;
            }
        }

        @media (max-width: 420px) {
            .fc-page {
                width: 100%;
                max-width: 420px;
                padding: 14px 10px 76px;
                overflow-x: hidden;
            }

            .fc-page > *,
            .fc-card,
            .news-card,
            .news-list,
            .news-item,
            .planning-card,
            .guide-main,
            .month-moment,
            .discovery-card {
                max-width: 100%;
                min-width: 0;
            }

            .fc-header {
                gap: 10px;
                margin-bottom: 12px;
                max-width: 100%;
            }

            .fc-brand {
                min-width: 0;
                font-size: 1.08rem;
            }

            .fc-brand-mark {
                width: 42px;
                height: 42px;
                border-radius: 14px;
            }

            .fc-header-actions {
                gap: 8px;
            }

            .fc-login,
            .fc-install {
                flex: 0 0 auto;
                padding: 10px 14px;
                border-radius: 14px;
                font-size: .9rem;
                max-width: 102px;
            }

            .fc-install span {
                display: none;
            }

            .fc-install {
                width: 42px;
                max-width: 42px;
                padding: 10px 0;
            }

            .news-card {
                min-height: 218px;
                padding: 16px;
                border-radius: 18px;
                overflow: hidden;
            }

            .app-hero {
                grid-template-columns: 1fr;
                gap: 12px;
                min-height: 0;
            }

            .news-content {
                max-width: 100%;
            }

            .news-visual {
                width: 104px;
                height: 104px;
                right: 0;
                top: 82px;
                opacity: .38;
            }

            h1 {
                font-size: 1.22rem;
                line-height: 1.18;
                max-width: 84%;
                margin-bottom: 9px;
                overflow: hidden;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
            }

            .app-hero h1 {
                max-width: 100%;
                -webkit-line-clamp: unset;
            }

            .hero-kicker {
                padding: 7px 9px;
                font-size: .66rem;
            }

            .hero-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .hero-actions .btn {
                min-height: 42px;
                padding: 8px 10px;
                font-size: .78rem;
                line-height: 1.08;
            }

            .hero-snapshot {
                display: none;
            }

            .hero-points {
                display: none;
            }

            .hero-showcase {
                min-height: 168px;
                place-items: end center;
                margin-top: -4px;
            }

            .phone-frame {
                width: min(136px, 46vw);
                padding: 6px;
                border-radius: 22px;
                transform: rotate(3deg);
            }

            .phone-frame img {
                border-radius: 17px;
            }

            .hero-mini-card {
                left: 12px;
                bottom: 10px;
                max-width: 142px;
                padding: 8px 9px;
                font-size: .6rem;
            }

            .source-pill {
                margin-bottom: 12px;
                padding: 8px 10px;
                font-size: .74rem;
            }

            .news-card p {
                max-width: 80%;
                font-size: .8rem;
                line-height: 1.3;
                -webkit-line-clamp: 2;
            }

            .carousel-meta {
                left: 18px;
                right: 18px;
                bottom: 12px;
            }

            .section-title {
                margin: 14px 0 8px;
                font-size: 1.06rem;
            }

            .news-list {
                gap: 10px;
            }

            .news-item {
                grid-template-columns: 84px minmax(0, 1fr) 14px;
                gap: 10px;
                padding: 10px;
                border-radius: 16px;
            }

            .news-item > div {
                min-width: 0;
            }

            .news-thumb {
                height: 70px;
                border-radius: 14px;
                font-size: 1.5rem;
            }

            .news-item h3 {
                font-size: .84rem;
                line-height: 1.22;
                margin-bottom: 5px;
            }

            .news-item strong {
                font-size: .74rem;
                margin-bottom: 2px;
            }

            .news-item small {
                font-size: .68rem;
            }

            .planning-card {
                padding: 14px;
                border-radius: 18px;
            }

            .steps {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
            }

            .feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
            }

            .feature-card {
                min-height: 104px;
                padding: 10px;
                border-radius: 14px;
            }

            .feature-card i {
                width: 30px;
                height: 30px;
                border-radius: 10px;
                margin-bottom: 7px;
                font-size: .9rem;
            }

            .feature-card strong {
                font-size: .74rem;
                line-height: 1.1;
            }

            .feature-card small {
                font-size: .6rem;
                line-height: 1.18;
            }

            .step {
                min-height: 92px;
                grid-template-columns: 1fr;
                align-content: start;
                gap: 7px;
                padding: 10px;
            }

            .step-number {
                width: 30px;
                height: 30px;
                font-size: .86rem;
            }

            .step-copy strong {
                font-size: .72rem;
                line-height: 1.08;
            }

            .step-copy small {
                font-size: .56rem;
                line-height: 1.15;
            }

            .step > i {
                justify-self: end;
                margin-top: -4px;
                font-size: .72rem;
                color: var(--cyan);
            }

            .guide-main {
                grid-template-columns: 108px minmax(0, 1fr);
                gap: 10px;
                padding: 10px;
                align-items: center;
            }

            .guide-thumb {
                width: 108px;
                height: 76px;
                border-radius: 12px;
            }

            .guide-copy h3 {
                font-size: .78rem;
                line-height: 1.18;
                margin-bottom: 4px;
                text-align: left;
            }

            .guide-copy p {
                font-size: .68rem;
                line-height: 1.22;
                margin-bottom: 4px;
            }

            .guide-copy a {
                font-size: .68rem;
                font-weight: 800;
                color: var(--cyan);
            }

            .guide-links {
                margin-top: 10px;
            }

            .guide-links a {
                padding: 10px 0;
                font-size: .76rem;
                line-height: 1.18;
            }

            .month-moment {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 16px 14px;
                align-items: stretch;
                align-content: start;
                border-radius: 18px;
                min-height: 0;
                margin-top: 14px;
            }

            .month-days {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                min-height: 62px;
                padding: 10px 14px;
                border-radius: 12px;
                align-self: stretch;
            }

            .month-days span {
                font-size: .62rem;
                white-space: nowrap;
            }

            .month-days strong {
                font-size: 1.95rem;
            }

            .month-copy {
                gap: 9px;
                min-width: 0;
                align-self: stretch;
                padding-top: 0;
            }

            .month-copy h3 {
                font-size: 1rem;
                line-height: 1.08;
                margin-top: 0;
            }

            .month-copy p {
                font-size: .72rem;
                line-height: 1.25;
            }

            .month-benefits {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 4px;
                margin-top: 4px;
            }

            .month-benefits span {
                display: grid;
                justify-items: center;
                gap: 3px;
                font-size: .52rem;
                text-align: center;
                line-height: 1.05;
                min-width: 0;
                overflow-wrap: anywhere;
            }

            .month-moment .btn {
                min-height: 42px;
                border-radius: 11px;
                font-size: .82rem;
                line-height: 1.05;
                padding: 8px 10px;
                width: 100%;
                grid-column: 1 / -1;
            }

            .month-moment .low-friction {
                font-size: .66rem;
                grid-column: 1 / -1;
            }

            .discovery-card {
                grid-template-columns: 42px minmax(0, 1fr) auto;
                gap: 10px;
                margin-top: 14px;
                padding: 14px;
                border-radius: 18px;
            }

            .discovery-card .fc-brand-mark {
                width: 42px;
                height: 42px;
            }

            .discovery-card h3 {
                font-size: 1rem;
            }

            .discovery-card p {
                font-size: .72rem;
                line-height: 1.25;
            }

            .discovery-card .btn {
                min-height: 38px;
                padding: 8px 10px;
                font-size: .72rem;
                border-radius: 12px;
                white-space: nowrap;
            }

            .cta-card {
                grid-template-columns: 1fr;
            }

            .bottom-nav {
                width: 100%;
                padding-left: 10px;
                padding-right: 10px;
            }
        }
    </style>
</head>
<body>
    <main class="fc-page">
        <header class="fc-header">
            <a class="fc-brand" href="01.home.php" aria-label="FinControle">
                <span class="fc-brand-mark"><i class="fa-solid fa-wallet"></i></span>
                <span>Fin<span>Controle</span></span>
            </a>
            <div class="fc-header-actions">
                <button type="button" class="fc-install" id="installAppButtonPwa" aria-label="Instalar aplicativo FinControle">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <span>Instalar app</span>
                </button>
                <a class="fc-login" href="02.login.php">Entrar</a>
            </div>
        </header>

        <section class="fc-card news-card app-hero" aria-label="FinControle">
            <img class="news-main-img" src="img/news-planning.jpg" alt="FinControle organizado no celular" onerror="this.style.display='none'">
            <div class="news-content">
                <div class="hero-kicker"><i class="fa-solid fa-wallet"></i> FinControle</div>
                <h1>Seu dinheiro e sua evolução mais fáceis de acompanhar.</h1>
                <p>Um app simples para ver o mês, registrar treinos, medir progresso e decidir com informação.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="06.cadastrar_usuario.php">Criar conta grátis</a>
                    <a class="btn btn-secondary" href="02.login.php">Entrar</a>
                </div>
                <div class="hero-snapshot" aria-label="Resumo das funções">
                    <span><strong>R$</strong><small>saldo, receitas e despesas</small></span>
                    <span><strong>ABC</strong><small>treino padrão e Full Body</small></span>
                    <span><strong>cm</strong><small>peso, abdômen e bíceps</small></span>
                </div>
            </div>
            <div class="hero-points">
                <span><i class="fa-solid fa-chart-line"></i> Relatórios para entender seu mês</span>
                <span><i class="fa-solid fa-dumbbell"></i> Treinos, histórico e medidas</span>
                <span><i class="fa-regular fa-newspaper"></i> Notícias sempre atualizadas</span>
            </div>
            <div class="hero-showcase" aria-hidden="true">
                <div class="phone-frame">
                    <img src="img/1-dash-exemplo.png" alt="">
                </div>
                <div class="hero-mini-card"><i class="fa-regular fa-circle-check"></i> Rotina organizada em poucos toques</div>
            </div>
        </section>

        <h2 class="section-title"><i class="fa-solid fa-layer-group"></i> O que você acompanha</h2>
        <section class="fc-card planning-card" aria-label="Funções do FinControle">
            <div class="feature-grid">
                <a class="feature-card" href="06.cadastrar_usuario.php"><i class="fa-solid fa-wallet"></i><strong>Finanças</strong><small>Saldo, receitas, despesas, filtros e vencimentos.</small></a>
                <a class="feature-card" href="06.cadastrar_usuario.php"><i class="fa-solid fa-dumbbell"></i><strong>Treinos</strong><small>Treino padrão, Full Body, execução e histórico.</small></a>
                <a class="feature-card" href="06.cadastrar_usuario.php"><i class="fa-solid fa-ruler-combined"></i><strong>Medidas</strong><small>Peso, abdômen, bíceps e evolução em gráficos.</small></a>
                <a class="feature-card" href="06.cadastrar_usuario.php"><i class="fa-solid fa-chart-column"></i><strong>Relatórios</strong><small>Resumo, categorias, tendência mensal e exportação.</small></a>
                <a class="feature-card" href="#noticias"><i class="fa-regular fa-newspaper"></i><strong>Notícias</strong><small>Finanças, carreira, saúde, tecnologia e gestão.</small></a>
            </div>
        </section>

        <h2 id="planejamento" class="section-title"><i class="fa-solid fa-star"></i> Comece pelo básico</h2>
        <section class="fc-card planning-card">
            <div class="steps">
                <a class="step" href="06.cadastrar_usuario.php">
                    <span class="step-number"><i class="fa-solid fa-calculator"></i></span>
                    <span class="step-copy">
                        <strong>Cadastre seu mês</strong>
                        <small>Receitas, despesas e contas</small>
                    </span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <a class="step" href="06.cadastrar_usuario.php">
                    <span class="step-number"><i class="fa-solid fa-dumbbell"></i></span>
                    <span class="step-copy">
                        <strong>Escolha o treino</strong>
                        <small>Padrão ou Full Body</small>
                    </span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <a class="step" href="06.cadastrar_usuario.php">
                    <span class="step-number"><i class="fa-solid fa-chart-line"></i></span>
                    <span class="step-copy">
                        <strong>Acompanhe evolução</strong>
                        <small>Relatórios, histórico e medidas</small>
                    </span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        </section>

        <h2 id="noticias" class="section-title"><i class="fa-regular fa-newspaper"></i> Notícias para decidir melhor</h2>
        <section class="news-list" aria-label="Notícias recentes">
            <?php foreach ($noticiasHome as $idx => $noticia): ?>
            <a class="fc-card news-item" href="<?= fincontrol_home_h(fincontrol_home_news_link($noticia)); ?>"<?= fincontrol_home_news_target($noticia); ?>>
                <div class="news-thumb has-img"><img src="<?= fincontrol_home_h($noticia['imagem'] ?? 'img/news-dollar.jpg'); ?>" alt="<?= fincontrol_home_h($noticia['titulo'] ?? 'Notícia'); ?>" onerror="this.src='img/news-dollar.jpg'"></div>
                <div>
                    <h3><?= fincontrol_home_h($noticia['titulo'] ?? 'Notícia para sua rotina'); ?></h3>
                    <strong><?= fincontrol_home_h($noticia['fonte'] ?? 'Fonte da notícia'); ?></strong>
                    <small><i class="fa-regular fa-clock"></i> <?= fincontrol_home_h($noticia['tempo'] ?? 'Hoje'); ?></small>
                </div>
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <?php endforeach; ?>
        </section>

        <section class="fc-card month-moment" aria-label="Planeje o restante do mês">
            <div class="month-copy">
                <h3><?= fincontrol_home_h($diasRestantesTexto); ?> para terminar <?= fincontrol_home_h($mesAtualHome); ?>.</h3>
                <p>Você sabe como estão seu dinheiro, seus compromissos e sua rotina?</p>
                <p>Use o FinControle para transformar registros simples em decisões melhores ao longo do mês.</p>
                <div class="month-benefits">
                    <span><i class="fa-regular fa-circle-check"></i> Veja seu mês financeiro</span>
                    <span><i class="fa-regular fa-circle-check"></i> Registre seus treinos</span>
                    <span><i class="fa-regular fa-circle-check"></i> Acompanhe sua evolução</span>
                </div>
            </div>
            <a class="btn btn-primary" href="06.cadastrar_usuario.php">Começar agora</a>
            <small class="low-friction"><i class="fa-solid fa-lock"></i> Gr&aacute;tis. Menos de 1 minuto.</small>
        </section>

        <section class="fc-card discovery-card" aria-label="Descubra para onde seu dinheiro está indo">
            <span class="fc-brand-mark"><i class="fa-solid fa-wallet"></i></span>
            <div>
                <h3>FinControle</h3>
                <p>Sua rotina mais simples de acompanhar.</p>
            </div>
            <a class="btn btn-secondary" href="06.cadastrar_usuario.php">Criar conta &rarr;</a>
        </section>
    </main>

    <nav class="bottom-nav" aria-label="Navegação principal">
        <a class="active" href="01.home.php"><i class="fa-solid fa-house"></i><span>Início</span></a>
        <a href="#noticias"><i class="fa-regular fa-newspaper"></i><span>Notícias</span></a>
        <a href="06.cadastrar_usuario.php"><i class="fa-regular fa-id-card"></i><span>Conta</span></a>
        <a href="02.login.php"><i class="fa-regular fa-user"></i><span>Entrar</span></a>
    </nav>

    <?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
    <script>
        const fincontrolNoticias = <?= json_encode(array_values($noticiasHome), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

        (() => {
            const card = document.getElementById('featuredNews');
            const image = document.getElementById('featuredNewsImage');
            const source = document.getElementById('featuredNewsSource');
            const title = document.getElementById('featuredNewsTitle');
            const summary = document.getElementById('featuredNewsSummary');
            const next = document.getElementById('featuredNewsNext');
            const dots = [...document.querySelectorAll('#featuredNewsDots i')];
            let current = 0;

            if (!card || !fincontrolNoticias.length) return;

            const isImageUrl = (url) => /\.(jpe?g|png|gif|webp|avif|svg)(?:[?#].*)?$/i.test(String(url || '').split('?')[0]);
            const newsLink = (item) => {
                const link = String(item?.link || '').trim();
                const imageUrl = String(item?.imagem || '').trim();
                return link && link !== imageUrl && !isImageUrl(link) ? link : '#noticias';
            };

            const render = (index) => {
                current = (index + fincontrolNoticias.length) % fincontrolNoticias.length;
                const item = fincontrolNoticias[current] || {};
                card.href = newsLink(item);
                image.src = item.imagem || 'img/news-selic.jpg';
                image.alt = item.titulo || 'Notícia financeira';
                source.textContent = item.fonte || 'Fonte da notícia';
                title.textContent = item.titulo || 'Notícia financeira para o seu bolso';
                summary.textContent = item.resumo || 'Acompanhe informações úteis para organizar melhor o mês.';
                dots.forEach((dot, dotIndex) => dot.classList.toggle('active', dotIndex === current));
            };

            next?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                render(current + 1);
            });

            dots.forEach((dot) => {
                dot.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    render(Number(dot.dataset.newsIndex || 0));
                });
            });
        })();

        document.querySelectorAll('.news-card, .news-item').forEach((newsAnchor) => {
            newsAnchor.addEventListener('click', (event) => {
                const href = newsAnchor.getAttribute('href') || '';
                if (/\.(jpe?g|png|gif|webp|avif|svg)(?:[?#].*)?$/i.test(href.split('?')[0])) {
                    event.preventDefault();
                    newsAnchor.setAttribute('href', '#noticias');
                }
            });
        });

        (() => {
            const installButton = document.getElementById('installAppButton');
            if (!installButton) return;

            let deferredPrompt = null;
            const isStandalone = () =>
                window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true ||
                document.referrer.startsWith('android-app://');

            const hideIfInstalled = () => {
                if (isStandalone()) installButton.hidden = true;
            };

            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                deferredPrompt = event;
                installButton.hidden = false;
            });

            window.addEventListener('appinstalled', () => {
                deferredPrompt = null;
                installButton.hidden = true;
            });

            installButton.addEventListener('click', async () => {
                if (isStandalone()) {
                    installButton.hidden = true;
                    return;
                }

                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    await deferredPrompt.userChoice.catch(() => undefined);
                    deferredPrompt = null;
                    return;
                }

                alert('Para instalar o FinControle, toque no menu do navegador e escolha "Instalar app" ou "Adicionar à tela inicial". Se essa opção não aparecer, atualize a página e tente novamente.');
            });

            hideIfInstalled();
        })();

        (() => {
            const installButton = document.getElementById('installAppButtonPwa');
            if (!installButton) return;

            const hideIfInstalled = () => {
                if (window.fincontrolIsStandalone && window.fincontrolIsStandalone()) {
                    installButton.hidden = true;
                }
            };

            window.addEventListener('fincontrol:pwa-ready', () => {
                installButton.hidden = false;
            });

            window.addEventListener('fincontrol:pwa-installed', () => {
                installButton.hidden = true;
            });

            installButton.addEventListener('click', async () => {
                if (window.fincontrolIsStandalone && window.fincontrolIsStandalone()) {
                    installButton.hidden = true;
                    return;
                }

                const originalText = installButton.innerHTML;
                installButton.disabled = true;
                installButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Instalando</span>';

                const result = window.fincontrolInstallApp
                    ? await window.fincontrolInstallApp()
                    : { status: 'unavailable' };

                installButton.disabled = false;
                installButton.innerHTML = originalText;

                if (result.status === 'accepted' || result.status === 'installed') {
                    installButton.hidden = true;
                    return;
                }

                alert('O navegador ainda nao liberou a instalacao automatica. Atualize esta pagina uma vez e toque em "Instalar app" novamente.');
            });

            hideIfInstalled();
        })();
    </script>
<script src="assets/noticias-atualizacao.js?v=2" data-news-revision="<?php echo fincontrol_home_h(fincontrol_noticias_revisao()); ?>" data-news-token="<?php echo fincontrol_home_h($_SESSION['noticias_csrf']); ?>" defer></script>
</body>
</html>
