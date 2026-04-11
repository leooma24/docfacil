<?php
/**
 * DocFácil — Kit de Vendedor (Excel)
 * Genera un archivo Excel listo para usar con todo lo necesario para vender DocFácil
 *
 * Uso: php docs/generate-vendedor-kit.php
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

$s = new Spreadsheet();

// Colors
$TEAL = '0D9488';
$CYAN = '0891B2';
$PURPLE = '7C3AED';
$LIGHT_BG = 'F0FDFA';
$DARK = '0F172A';
$GOLD = 'F59E0B';

/* ========== HOJA 1: PORTADA ========== */
$s->getActiveSheet()->setTitle('Portada');
$ws = $s->getActiveSheet();
$ws->mergeCells('A1:F1');
$ws->setCellValue('A1', 'DocFácil — Kit de Vendedor 2026');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(28)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($TEAL);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(60);

$ws->mergeCells('A2:F2');
$ws->setCellValue('A2', 'El único software médico con IA de verdad en México');
$ws->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($CYAN);
$ws->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(2)->setRowHeight(25);

// Table of contents
$toc = [
    ['', ''],
    ['ÍNDICE DEL KIT', ''],
    ['1. Planes y Precios', 'Tabla completa con todos los planes, precios y features'],
    ['2. Features por Plan', 'Comparativa detallada de qué incluye cada plan'],
    ['3. Features con IA', '12 features con Inteligencia Artificial'],
    ['4. Competencia', 'Comparativa vs SimplyBook, Abysmed, Doctoralia, iPraxis'],
    ['5. Script de Venta', 'Argumentos clave y respuestas a objeciones'],
    ['6. Calculadora ROI', 'Hoja interactiva para calcular ahorro del doctor'],
    ['7. Comisiones', 'Cuánto ganas por cada venta (50% primer pago, 50% segundo)'],
    ['8. Links y Demos', 'URLs importantes: demo, landing, WhatsApp soporte'],
    ['9. Checklist de Venta', 'Pasos para cerrar un cliente'],
];

$row = 4;
foreach ($toc as [$a, $b]) {
    $ws->setCellValue("A{$row}", $a);
    $ws->setCellValue("B{$row}", $b);
    if ($a && !$b) {
        $ws->mergeCells("A{$row}:F{$row}");
        $ws->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($DARK);
    } else {
        $ws->getStyle("A{$row}")->getFont()->setBold(true)->getColor()->setRGB($TEAL);
        $ws->mergeCells("B{$row}:F{$row}");
    }
    $row++;
}

$ws->getColumnDimension('A')->setWidth(28);
$ws->getColumnDimension('B')->setWidth(20);
$ws->getColumnDimension('C')->setWidth(20);
$ws->getColumnDimension('D')->setWidth(20);
$ws->getColumnDimension('E')->setWidth(20);
$ws->getColumnDimension('F')->setWidth(20);

/* ========== HOJA 2: PLANES Y PRECIOS ========== */
$s->createSheet()->setTitle('Planes y Precios');
$ws = $s->getSheet(1);

$ws->mergeCells('A1:F1');
$ws->setCellValue('A1', '💰 PLANES Y PRECIOS');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($TEAL);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$headers = ['Plan', 'Precio /mes', 'Precio Fundador', 'Doctores', 'Pacientes', 'Target'];
foreach ($headers as $i => $h) {
    $col = chr(65 + $i);
    $ws->setCellValue("{$col}3", $h);
}
$ws->getStyle('A3:F3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A3:F3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
$ws->getRowDimension(3)->setRowHeight(25);

$planes = [
    ['FREE', '$0', '—', '1', '30', 'Doctores curiosos. Gancho para conversión.'],
    ['BÁSICO', '$299', '$149 (50% off)', '1', '200', 'Doctores individuales que buscan eficiencia básica.'],
    ['PRO ⭐', '$599', '$299 (50% off)', '3', 'Ilimitados', 'EL QUE MÁS VENDE. Incluye TODA la IA.'],
    ['CLÍNICA', '$1,199', '$599 (50% off)', 'Ilimitados', 'Ilimitados', 'Clínicas con 4+ doctores. Multi-sucursal.'],
];

$row = 4;
foreach ($planes as $p) {
    foreach ($p as $i => $val) {
        $col = chr(65 + $i);
        $ws->setCellValue("{$col}{$row}", $val);
    }
    // Highlight PRO row
    if (str_contains($p[0], 'PRO')) {
        $ws->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($LIGHT_BG);
        $ws->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);
    }
    $ws->getStyle("A{$row}:F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $row++;
}

// Comisiones nota
$row += 2;
$ws->mergeCells("A{$row}:F{$row}");
$ws->setCellValue("A{$row}", '⚠️ IMPORTANTE: Las comisiones solo se pagan por planes PRO y CLÍNICA (NO Free ni Básico)');
$ws->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('DC2626');
$ws->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
$ws->getRowDimension($row)->setRowHeight(25);

for ($c = 'A'; $c <= 'F'; $c++) {
    $ws->getColumnDimension($c)->setWidth(18);
}
$ws->getColumnDimension('A')->setWidth(14);
$ws->getColumnDimension('B')->setWidth(14);
$ws->getColumnDimension('C')->setWidth(18);
$ws->getColumnDimension('F')->setWidth(45);

/* ========== HOJA 3: FEATURES POR PLAN ========== */
$s->createSheet()->setTitle('Features por Plan');
$ws = $s->getSheet(2);

$ws->mergeCells('A1:E1');
$ws->setCellValue('A1', '✨ FEATURES INCLUIDAS POR PLAN');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($TEAL);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$ws->setCellValue('A3', 'Feature');
$ws->setCellValue('B3', 'Free');
$ws->setCellValue('C3', 'Básico $299');
$ws->setCellValue('D3', 'Pro $599');
$ws->setCellValue('E3', 'Clínica $1,199');
$ws->getStyle('A3:E3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A3:E3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
$ws->getStyle('A3:E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(3)->setRowHeight(25);

$features = [
    // [feature, free, básico, pro, clínica]
    ['👥 Pacientes', '30', '200', 'Ilimitados', 'Ilimitados'],
    ['📅 Citas por mes', '20', 'Ilimitadas', 'Ilimitadas', 'Ilimitadas'],
    ['👨‍⚕️ Doctores', '1', '1', '3', 'Ilimitados'],
    ['🏢 Multi-sucursal', '❌', '❌', '❌', '✅'],
    ['📄 Recetas PDF profesionales', '❌', '✅', '✅', '✅'],
    ['📱 Recordatorios WhatsApp', '❌', '✅', '✅', '✅'],
    ['💬 Cobro por WhatsApp', '❌', '✅', '✅', '✅'],
    ['📲 Check-in con QR', '❌', '✅', '✅', '✅'],
    ['🎤 Dictado por voz', '❌', '✅', '✅', '✅'],
    ['✍️ Firma digital', '❌', '✅', '✅', '✅'],
    ['🦷 Odontograma', '❌', '✅', '✅', '✅'],
    ['', '', '', '', ''],
    ['════ FEATURES CON IA ════', '', '', '', ''],
    ['🤖 Dictado inteligente con IA', '❌', '❌', '✅', '✅'],
    ['🧠 Resumen IA del paciente', '❌', '❌', '✅', '✅'],
    ['💡 Sugerencias de diagnóstico IA', '❌', '❌', '✅', '✅'],
    ['📋 Consentimientos con IA', '❌', '❌', '✅', '✅'],
    ['📊 Análisis IA del consultorio', '❌', '❌', '✅', '✅'],
    ['🔮 Métricas predictivas', '❌', '❌', '✅', '✅'],
    ['💬 Chatbot IA flotante', '❌', '❌', '✅', '✅'],
    ['🎙️ Modo consulta en vivo', '❌', '❌', '✅', '✅'],
    ['⌨️ Command Palette (Ctrl+K)', '❌', '❌', '✅', '✅'],
    ['🗓️ Slot mágico en calendario', '❌', '❌', '✅', '✅'],
    ['💬 Autoresponder WhatsApp IA', '❌', '❌', '✅', '✅'],
    ['📝 Generador mensajes por paciente', '❌', '❌', '✅', '✅'],
    ['', '', '', '', ''],
    ['════ PREMIUM ════', '', '', '', ''],
    ['📈 Reportes por doctor', '❌', '❌', '❌', '✅'],
    ['💰 Comisiones entre doctores', '❌', '❌', '❌', '✅'],
    ['⭐ Soporte prioritario', '❌', '❌', '❌', '✅'],
    ['🎯 Onboarding 1 a 1', '❌', '❌', '❌', '✅'],
];

$row = 4;
foreach ($features as $f) {
    foreach ($f as $i => $val) {
        $col = chr(65 + $i);
        $ws->setCellValue("{$col}{$row}", $val);
    }
    // Section headers
    if (str_contains($f[0], '════')) {
        $ws->mergeCells("A{$row}:E{$row}");
        $ws->getStyle("A{$row}")->getFont()->setBold(true)->getColor()->setRGB($PURPLE);
        $ws->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3E8FF');
        $ws->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    } else {
        $ws->getStyle("A{$row}:E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $ws->getStyle("B{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    $row++;
}

$ws->getColumnDimension('A')->setWidth(40);
$ws->getColumnDimension('B')->setWidth(12);
$ws->getColumnDimension('C')->setWidth(16);
$ws->getColumnDimension('D')->setWidth(16);
$ws->getColumnDimension('E')->setWidth(18);

/* ========== HOJA 4: FEATURES CON IA ========== */
$s->createSheet()->setTitle('Features IA');
$ws = $s->getSheet(3);

$ws->mergeCells('A1:D1');
$ws->setCellValue('A1', '🤖 FEATURES CON INTELIGENCIA ARTIFICIAL');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($PURPLE);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$ws->setCellValue('A3', 'Feature');
$ws->setCellValue('B3', 'Qué hace');
$ws->setCellValue('C3', 'Ahorro al doctor');
$ws->setCellValue('D3', 'Pitch killer');
$ws->getStyle('A3:D3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A3:D3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
$ws->getRowDimension(3)->setRowHeight(25);

$ai = [
    ['Dictado inteligente', 'Habla toda la consulta y la IA llena diagnóstico, tratamiento, receta y notas automáticamente.', '5 min/paciente', '"Doctor, deja de teclear. Habla y yo escribo."'],
    ['Resumen IA paciente', 'Al abrir un paciente, la IA te da un resumen narrativo en 2 segundos.', '30 seg/paciente', '"Entiende al paciente en 2 segundos sin leer 5 pantallas."'],
    ['Sugerencias Dx', 'Escribes el motivo y la IA sugiere 3 diagnósticos con tratamiento y medicamento.', 'Consultas difíciles', '"Es como tener un residente experto al lado."'],
    ['Consentimientos IA', 'Escribes el procedimiento y la IA genera el consentimiento completo en 5 segundos.', '20 min/consentimiento', '"De 20 minutos a 5 segundos."'],
    ['Análisis consultorio', 'La IA analiza tus datos y sugiere acciones: qué pacientes recuperar, cuándo subir precios, etc.', '—', '"Como tener un consultor de negocio 24/7."'],
    ['Métricas predictivas', 'Predice ingresos, detecta pacientes que se van, sugiere precios óptimos.', '—', '"DocFácil te dice qué va a pasar en 2 semanas."'],
    ['Chatbot IA flotante', 'Pregunta lo que sea: "cuánto facturé", "quién tiene cita mañana". Responde con datos reales.', '20 clicks/día', '"En lugar de navegar menús, le preguntas."'],
    ['Modo consulta en vivo', 'La IA escucha toda la consulta (doctor+paciente) y llena el expediente al final.', '5 min/paciente', '"Tú atiendes, la IA escribe."'],
    ['Command Palette', 'Ctrl+K en cualquier pantalla. Escribe lo que quieres hacer y aparece.', '50 clicks/día', '"Como Linear o Notion, pero para tu consultorio."'],
    ['Slot mágico calendario', 'Dile "busca 30min mañana" y la IA te da los mejores horarios.', 'Reagendado más rápido', '"Nunca más tetris mental con la agenda."'],
    ['Autoresponder WhatsApp', 'Pacientes te escriben y la IA responde en tu nombre sobre citas, recetas, etc.', 'Recepcionista virtual', '"Tu clínica contesta WhatsApp 24/7 sin contratar a nadie."'],
    ['Generador mensajes', 'Botón para generar mensaje perfecto para cada paciente: recordatorio, seguimiento, cumpleaños, etc.', '10 min/mensaje', '"Retén pacientes sin escribir tú."'],
];

$row = 4;
foreach ($ai as $f) {
    foreach ($f as $i => $val) {
        $col = chr(65 + $i);
        $ws->setCellValue("{$col}{$row}", $val);
    }
    $ws->getStyle("A{$row}")->getFont()->setBold(true)->getColor()->setRGB($PURPLE);
    $ws->getStyle("D{$row}")->getFont()->setItalic(true)->getColor()->setRGB($TEAL);
    $ws->getStyle("A{$row}:D{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $ws->getStyle("A{$row}:D{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
    $ws->getRowDimension($row)->setRowHeight(50);
    $row++;
}

$ws->getColumnDimension('A')->setWidth(25);
$ws->getColumnDimension('B')->setWidth(50);
$ws->getColumnDimension('C')->setWidth(25);
$ws->getColumnDimension('D')->setWidth(50);

/* ========== HOJA 5: COMPETENCIA ========== */
$s->createSheet()->setTitle('vs Competencia');
$ws = $s->getSheet(4);

$ws->mergeCells('A1:G1');
$ws->setCellValue('A1', '⚔️ DOCFÁCIL vs COMPETENCIA');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$headers = ['Software', 'Precio /mes', 'IA', 'Dictado', 'WhatsApp', 'Recetas PDF', 'Nube'];
foreach ($headers as $i => $h) {
    $col = chr(65 + $i);
    $ws->setCellValue("{$col}3", $h);
}
$ws->getStyle('A3:G3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
$ws->getStyle('A3:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$compet = [
    ['DocFácil PRO ⭐', '$599', '✅✅✅ 12 features', '✅', '✅ Automático', '✅', '✅'],
    ['SimplyBook.me', '$800+', '❌', '❌', '⚠️ Básico', '⚠️', '✅'],
    ['Abysmed', '$1,200+', '❌', '❌', '❌', '✅', '✅'],
    ['Doctoralia', '$1,500+', '❌', '❌', '⚠️ Básico', '❌', '✅'],
    ['iPraxis', '$700', '❌', '❌', '❌', '✅', '❌ Local'],
];

$row = 4;
foreach ($compet as $c) {
    foreach ($c as $i => $val) {
        $col = chr(65 + $i);
        $ws->setCellValue("{$col}{$row}", $val);
    }
    if (str_contains($c[0], 'DocFácil')) {
        $ws->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($LIGHT_BG);
        $ws->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
    }
    $ws->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $ws->getStyle("B{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
}

$row += 2;
$ws->mergeCells("A{$row}:G{$row}");
$ws->setCellValue("A{$row}", '💡 KEY INSIGHT: Eres el MÁS BARATO Y tienes las ÚNICAS features con IA del mercado mexicano.');
$ws->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($TEAL);
$ws->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension($row)->setRowHeight(30);

for ($c = 'A'; $c <= 'G'; $c++) {
    $ws->getColumnDimension($c)->setWidth(16);
}
$ws->getColumnDimension('A')->setWidth(20);
$ws->getColumnDimension('C')->setWidth(20);

/* ========== HOJA 6: SCRIPT DE VENTA ========== */
$s->createSheet()->setTitle('Script de Venta');
$ws = $s->getSheet(5);

$ws->mergeCells('A1:B1');
$ws->setCellValue('A1', '📞 SCRIPT DE VENTA + OBJECIONES');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($TEAL);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$script = [
    ['APERTURA', ''],
    ['Saludo inicial', '"Hola Dr./Dra. [Nombre], soy [Tu nombre] de DocFácil. Te contacto porque somos el único software médico en México con Inteligencia Artificial integrada. ¿Tienes 3 minutos para ver cómo le ahorramos 2 horas al día a doctores como tú?"'],
    ['', ''],
    ['DOLOR', ''],
    ['Pregunta clave', '"Doctor, ¿cuánto tiempo al día pasas tecleando expedientes en vez de atender pacientes?"'],
    ['Pregunta 2', '"¿Cuántos pacientes se te van porque no se enteran que tienen cita?"'],
    ['Pregunta 3', '"¿Cobras efectivo o pierdes ventas porque el paciente no trae dinero?"'],
    ['', ''],
    ['DEMO', ''],
    ['Punto 1', 'Muestra DICTADO INTELIGENTE: "Mira, oprimo grabar, hablo normal y la IA me llena todo: diagnóstico, tratamiento, receta."'],
    ['Punto 2', 'Muestra CHATBOT IA: "Ctrl+K, le pregunto cuánto facturé este mes y me responde con datos reales."'],
    ['Punto 3', 'Muestra RECORDATORIOS: "Cada cita genera recordatorio automático 24h y 2h antes por WhatsApp."'],
    ['Punto 4', 'Muestra COBRO WhatsApp: "Al terminar la consulta, un click y el paciente recibe el cobro."'],
    ['', ''],
    ['CIERRE', ''],
    ['Frase de cierre', '"Doctor, por $599 al mes estás ganando 2 horas al día = $10,000 extra al mes. DocFácil se paga 16 veces."'],
    ['Call to action', '"Te doy 14 días gratis con todas las features. Sin tarjeta. ¿A qué hora te puedo ayudar a configurarlo?"'],
    ['', ''],
    ['OBJECIONES', ''],
    ['"Es caro"', '"Doctor, ¿cuánto cobras por consulta? ¿$500? DocFácil Pro cuesta MENOS de 1 consulta y te ahorra 40 pacientes al mes en tiempo. Se paga 40 veces."'],
    ['"No soy tecnológico"', '"Por eso la IA. No tienes que aprender nada. Literalmente hablas y la app escribe. Si sabes usar WhatsApp, sabes usar DocFácil."'],
    ['"Ya tengo software"', '"¿Tiene IA que escribe expedientes? ¿Tiene chatbot que responde preguntas? ¿Predice tus ingresos? Somos los únicos. Pruébalo 14 días gratis sin cancelar lo actual."'],
    ['"Mis datos están seguros?"', '"Absolutamente. Encriptación SSL, backups automáticos diarios, tus datos están separados de otras clínicas. Cumplimos con todas las normas mexicanas."'],
    ['"Y si no me gusta?"', '"Cancelas cuando quieras, sin penalización. Primeros 14 días son gratis sin tarjeta. Solo pagas si te gusta."'],
    ['"Necesito pensarlo"', '"Entiendo doctor. Te activo los 14 días gratis ya, pruébalo con tus pacientes de mañana, si no te gusta, no pasa nada. ¿Cuál es tu WhatsApp?"'],
];

$row = 3;
foreach ($script as [$k, $v]) {
    $ws->setCellValue("A{$row}", $k);
    $ws->setCellValue("B{$row}", $v);
    if ($k && !$v) {
        $ws->mergeCells("A{$row}:B{$row}");
        $ws->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $ws->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($PURPLE);
        $ws->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    } else {
        $ws->getStyle("A{$row}")->getFont()->setBold(true)->getColor()->setRGB($TEAL);
        $ws->getStyle("A{$row}:B{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $ws->getStyle("A{$row}:B{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $ws->getRowDimension($row)->setRowHeight(50);
    }
    $row++;
}

$ws->getColumnDimension('A')->setWidth(25);
$ws->getColumnDimension('B')->setWidth(90);

/* ========== HOJA 7: CALCULADORA ROI ========== */
$s->createSheet()->setTitle('Calculadora ROI');
$ws = $s->getSheet(6);

$ws->mergeCells('A1:C1');
$ws->setCellValue('A1', '💸 CALCULADORA DE ROI (edita las celdas amarillas)');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(18)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($GOLD);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

// Inputs
$ws->setCellValue('A3', 'INPUTS DEL DOCTOR');
$ws->getStyle('A3')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($DARK);
$ws->mergeCells('A3:C3');

$ws->setCellValue('A4', 'Pacientes al mes');
$ws->setCellValue('B4', 80);
$ws->setCellValue('C4', 'Pacientes que atiendes mensualmente');

$ws->setCellValue('A5', 'Precio por consulta (MXN)');
$ws->setCellValue('B5', 500);
$ws->setCellValue('C5', 'Lo que cobras por consulta promedio');

$ws->setCellValue('A6', 'Horas/semana en papeleo');
$ws->setCellValue('B6', 10);
$ws->setCellValue('C6', 'Horas que gastas en admin, no atendiendo');

$ws->getStyle('B4:B6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
$ws->getStyle('B4:B6')->getFont()->setBold(true)->setSize(14);
$ws->getStyle('A4:C6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Outputs
$ws->setCellValue('A8', 'CÁLCULO DE AHORRO');
$ws->getStyle('A8')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($TEAL);
$ws->mergeCells('A8:C8');

$ws->setCellValue('A9', 'Ahorro en tiempo (DocFácil reduce 60% admin)');
$ws->setCellValue('B9', '=B6*0.6*4*(B5/0.5)');
$ws->setCellValue('C9', 'Horas liberadas x tarifa/hora');

$ws->setCellValue('A10', 'Retención por WhatsApp (reduce 8% no-shows)');
$ws->setCellValue('B10', '=B4*0.08*B5');
$ws->setCellValue('C10', 'Citas que se salvan x precio');

$ws->setCellValue('A11', 'Extra por dictado IA (atiendes más pacientes)');
$ws->setCellValue('B11', '=20*B5*0.15');
$ws->setCellValue('C11', '1 paciente extra al día x precio');

$ws->setCellValue('A12', 'TOTAL AHORRO/MES');
$ws->setCellValue('B12', '=B9+B10+B11');
$ws->setCellValue('C12', 'Total mensual ganado con DocFácil');
$ws->getStyle('A12:C12')->getFont()->setBold(true)->setSize(14);
$ws->getStyle('A12:C12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($LIGHT_BG);

$ws->getStyle('B9:B12')->getNumberFormat()->setFormatCode('"$"#,##0');
$ws->getStyle('A9:C12')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// ROI
$ws->setCellValue('A14', 'Precio DocFácil Pro');
$ws->setCellValue('B14', 599);
$ws->getStyle('B14')->getNumberFormat()->setFormatCode('"$"#,##0');

$ws->setCellValue('A15', 'ROI (cuántas veces paga DocFácil)');
$ws->setCellValue('B15', '=B12/B14');
$ws->getStyle('B15')->getNumberFormat()->setFormatCode('0.0"x"');
$ws->getStyle('A15:B15')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB($TEAL);

$ws->getColumnDimension('A')->setWidth(40);
$ws->getColumnDimension('B')->setWidth(18);
$ws->getColumnDimension('C')->setWidth(45);

/* ========== HOJA 8: COMISIONES ========== */
$s->createSheet()->setTitle('Comisiones');
$ws = $s->getSheet(7);

$ws->mergeCells('A1:D1');
$ws->setCellValue('A1', '💰 ESQUEMA DE COMISIONES');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($GOLD);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$ws->mergeCells('A3:D3');
$ws->setCellValue('A3', '🎯 GANAS 1.5 MENSUALIDADES POR CADA CLIENTE PRO o CLÍNICA');
$ws->getStyle('A3')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB($TEAL);
$ws->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$ws->mergeCells('A4:D4');
$ws->setCellValue('A4', 'Se paga 50% con el primer pago del cliente, 50% con el segundo pago');
$ws->getStyle('A4')->getFont()->setItalic(true)->getColor()->setRGB('64748B');
$ws->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$headers = ['Plan', 'Precio cliente/mes', 'Comisión total (1.5x)', '½ primer pago + ½ segundo pago'];
foreach ($headers as $i => $h) {
    $col = chr(65 + $i);
    $ws->setCellValue("{$col}6", $h);
}
$ws->getStyle('A6:D6')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A6:D6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
$ws->getStyle('A6:D6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(6)->setRowHeight(25);

$coms = [
    ['Free', '$0', '$0', 'Sin comisión'],
    ['Básico', '$299', '$0', 'Sin comisión'],
    ['Pro ⭐', '$599', '$898.50', '$449.25 + $449.25'],
    ['Clínica', '$1,199', '$1,798.50', '$899.25 + $899.25'],
];

$row = 7;
foreach ($coms as $c) {
    foreach ($c as $i => $val) {
        $col = chr(65 + $i);
        $ws->setCellValue("{$col}{$row}", $val);
    }
    if (str_contains($c[0], 'Pro') || str_contains($c[0], 'Clínica')) {
        $ws->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($LIGHT_BG);
        $ws->getStyle("A{$row}:D{$row}")->getFont()->setBold(true);
    }
    $ws->getStyle("A{$row}:D{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $ws->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
}

$row += 2;
$ws->mergeCells("A{$row}:D{$row}");
$ws->setCellValue("A{$row}", '📊 METAS MENSUALES PARA VENDEDOR');
$ws->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($DARK);
$ws->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row += 2;

$metas = [
    ['Nivel', 'Ventas/mes', 'Mix', 'Comisión aproximada'],
    ['Starter', '3 Pro', '3 x $898', '$2,695'],
    ['Medium', '5 Pro + 1 Clínica', '5x$898 + 1x$1,798', '$6,290'],
    ['Top', '10 Pro + 2 Clínica', '10x$898 + 2x$1,798', '$12,580'],
];

foreach ($metas as $i => $m) {
    foreach ($m as $j => $val) {
        $col = chr(65 + $j);
        $ws->setCellValue("{$col}{$row}", $val);
    }
    if ($i === 0) {
        $ws->getStyle("A{$row}:D{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $ws->getStyle("A{$row}:D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
    }
    $ws->getStyle("A{$row}:D{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $ws->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
}

$ws->getColumnDimension('A')->setWidth(18);
$ws->getColumnDimension('B')->setWidth(22);
$ws->getColumnDimension('C')->setWidth(28);
$ws->getColumnDimension('D')->setWidth(25);

/* ========== HOJA 9: LINKS Y DEMOS ========== */
$s->createSheet()->setTitle('Links y Recursos');
$ws = $s->getSheet(8);

$ws->mergeCells('A1:B1');
$ws->setCellValue('A1', '🔗 LINKS IMPORTANTES');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($TEAL);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$links = [
    ['Landing page', 'https://docfacil.tu-app.co'],
    ['Demo para vendedor (crea clínica temporal)', 'https://docfacil.tu-app.co/demo-vendedor'],
    ['Demo público para prospecto', 'https://docfacil.tu-app.co/demo'],
    ['Registro gratis (doctor)', 'https://docfacil.tu-app.co/doctor/register'],
    ['Panel de admin de doctor', 'https://docfacil.tu-app.co/doctor'],
    ['WhatsApp soporte', 'https://wa.me/526682493398'],
    ['Términos y condiciones', 'https://docfacil.tu-app.co/terminos'],
    ['Política de privacidad', 'https://docfacil.tu-app.co/privacidad'],
];

$row = 3;
foreach ($links as [$label, $url]) {
    $ws->setCellValue("A{$row}", $label);
    $ws->setCellValue("B{$row}", $url);
    $ws->getStyle("A{$row}")->getFont()->setBold(true);
    $ws->getStyle("B{$row}")->getFont()->getColor()->setRGB($TEAL);
    $ws->getStyle("A{$row}:B{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $ws->getRowDimension($row)->setRowHeight(25);
    $row++;
}

$ws->getColumnDimension('A')->setWidth(40);
$ws->getColumnDimension('B')->setWidth(60);

/* ========== HOJA 10: CHECKLIST DE VENTA ========== */
$s->createSheet()->setTitle('Checklist');
$ws = $s->getSheet(9);

$ws->mergeCells('A1:C1');
$ws->setCellValue('A1', '✅ CHECKLIST PARA CERRAR UNA VENTA');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($TEAL);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$checklist = [
    ['☐', 'Antes del primer contacto', 'Investigar la clínica: especialidad, tamaño, redes sociales'],
    ['☐', 'Abrir demo-vendedor', 'Tener la sesión demo lista en otra pestaña'],
    ['☐', 'Contacto inicial', 'Llamada o WhatsApp al doctor (NO email frío)'],
    ['☐', 'Romper el hielo', 'Hacer pregunta sobre su dolor (tiempo, no-shows, cobros)'],
    ['☐', 'Demo en vivo', 'Mostrar dictado IA + chatbot IA + recordatorios'],
    ['☐', 'Cotización', 'Mostrar Plan Pro $599 con 50% descuento = $299 de por vida (fundador)'],
    ['☐', 'Objeciones', 'Usar respuestas del script (hoja "Script de Venta")'],
    ['☐', 'Ofrecer trial 14 días', 'Sin tarjeta de crédito, sin compromiso'],
    ['☐', 'Crear cuenta en su presencia', 'Registrarlo en docfacil.tu-app.co/doctor/register?vnd=TU_CODIGO'],
    ['☐', 'Configurar con él', 'Ayudar a subir 5-10 pacientes iniciales'],
    ['☐', 'Agendar seguimiento', 'Llamada en 3 días para ver cómo va'],
    ['☐', 'Cierre formal', 'Al día 12: "¿Qué te parece si activamos el plan Pro?"'],
    ['☐', 'Primer pago', 'Se carga tu comisión del 50% (primera mitad)'],
    ['☐', 'Seguimiento mes 2', 'Asegurar que renueva para recibir la segunda mitad de comisión'],
];

$row = 3;
foreach ($checklist as [$chk, $step, $desc]) {
    $ws->setCellValue("A{$row}", $chk);
    $ws->setCellValue("B{$row}", $step);
    $ws->setCellValue("C{$row}", $desc);
    $ws->getStyle("A{$row}")->getFont()->setSize(16);
    $ws->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $ws->getStyle("B{$row}")->getFont()->setBold(true)->getColor()->setRGB($TEAL);
    $ws->getStyle("A{$row}:C{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $ws->getRowDimension($row)->setRowHeight(25);
    $row++;
}

$ws->getColumnDimension('A')->setWidth(6);
$ws->getColumnDimension('B')->setWidth(30);
$ws->getColumnDimension('C')->setWidth(60);

/* ========== SAVE ========== */
$writer = new Xlsx($s);
$outputPath = __DIR__ . '/DocFacil-Kit-Vendedor-2026.xlsx';
$writer->save($outputPath);

echo "✅ Excel generado: {$outputPath}\n";
echo "📊 10 hojas con toda la información para cerrar ventas\n";
echo "🚀 Listo para compartir con vendedores\n";
