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
$ws->setCellValue('A2', 'Software para consultorios médicos y dentales en México');
$ws->getStyle('A2')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($CYAN);
$ws->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(2)->setRowHeight(25);

// Table of contents
$toc = [
    ['', ''],
    ['ÍNDICE DEL KIT', ''],
    ['1. Planes y Precios', 'Tabla completa con todos los planes, precios y features'],
    ['2. Features por Plan', 'Comparativa detallada de qué incluye cada plan'],
    ['3. Competencia', 'Comparativa vs SimplyBook, Abysmed, Doctoralia, iPraxis'],
    ['4. Script de Venta', 'Argumentos clave y respuestas a objeciones'],
    ['5. Calculadora ROI', 'Hoja interactiva para calcular ahorro del doctor'],
    ['6. Comisiones', 'Cuánto ganas por cada venta (50% primer pago, 50% segundo)'],
    ['7. Links y Demos', 'URLs importantes: demo, landing, WhatsApp soporte'],
    ['8. Checklist de Venta', 'Pasos para cerrar un cliente'],
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
    ['FREE', '$0', '—', '1', '30', 'Doctores curiosos. Sin comisión. Gancho para conversión.'],
    ['BÁSICO', '$149', '$74 (50% off)', '1', '200', 'Doctores individuales. Comisión: $223.50 por venta.'],
    ['PRO ⭐', '$299', '$149 (50% off)', '3', 'Ilimitados', 'EL QUE MÁS VENDE. Odontograma + Portal + Multi-doctor. Comisión: $448.50 por venta.'],
    ['CLÍNICA', '$499', '$249 (50% off)', 'Ilimitados', 'Ilimitados', 'Clínicas grandes. Comisión: $748.50 por venta.'],
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
$ws->setCellValue("A{$row}", '✅ TODOS los planes de pago (Básico, Pro y Clínica) pagan comisión. Solo el plan Free NO.');
$ws->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('065F46');
$ws->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
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
$ws->setCellValue('C3', 'Básico $149');
$ws->setCellValue('D3', 'Pro $299');
$ws->setCellValue('E3', 'Clínica $499');
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
    ['', '', '', '', ''],
    ['════ ESENCIALES ════', '', '', '', ''],
    ['📄 Recetas PDF profesionales', '❌', '✅', '✅', '✅'],
    ['📱 Recordatorios WhatsApp', '❌', '✅', '✅', '✅'],
    ['💬 Cobro por WhatsApp', '❌', '✅', '✅', '✅'],
    ['📲 Check-in con QR', '❌', '✅', '✅', '✅'],
    ['📋 Expediente clínico completo', '✅', '✅', '✅', '✅'],
    ['', '', '', '', ''],
    ['════ PRO ════', '', '', '', ''],
    ['🦷 Odontograma interactivo', '❌', '❌', '✅', '✅'],
    ['✍️ Firma digital', '❌', '❌', '✅', '✅'],
    ['📋 Consentimientos digitales', '❌', '❌', '✅', '✅'],
    ['👥 Portal del paciente', '❌', '❌', '✅', '✅'],
    ['📊 Reportes avanzados', '❌', '❌', '✅', '✅'],
    ['', '', '', '', ''],
    ['════ CLÍNICA ════', '', '', '', ''],
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

/* ========== HOJA 4: COMPETENCIA ========== */
$s->createSheet()->setTitle('vs Competencia');
$ws = $s->getSheet(3);

$ws->mergeCells('A1:G1');
$ws->setCellValue('A1', '⚔️ DOCFÁCIL vs COMPETENCIA');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$headers = ['Software', 'Precio /mes', 'Odontograma', 'Firma digital', 'WhatsApp', 'Recetas PDF', 'Nube'];
foreach ($headers as $i => $h) {
    $col = chr(65 + $i);
    $ws->setCellValue("{$col}3", $h);
}
$ws->getStyle('A3:G3')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($DARK);
$ws->getStyle('A3:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$compet = [
    ['DocFácil PRO ⭐', '$299', '✅', '✅', '✅ Automático', '✅', '✅'],
    ['SimplyBook.me', '$800+', '❌', '❌', '⚠️ Básico', '⚠️', '✅'],
    ['Abysmed', '$1,200+', '✅', '⚠️', '❌', '✅', '✅'],
    ['Doctoralia', '$1,500+', '❌', '❌', '⚠️ Básico', '❌', '✅'],
    ['iPraxis', '$700', '✅', '❌', '❌', '✅', '❌ Local'],
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
$ws->setCellValue("A{$row}", '💡 KEY INSIGHT: Eres el MÁS BARATO con odontograma, firma digital y WhatsApp automático del mercado mexicano.');
$ws->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($TEAL);
$ws->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension($row)->setRowHeight(30);

for ($c = 'A'; $c <= 'G'; $c++) {
    $ws->getColumnDimension($c)->setWidth(16);
}
$ws->getColumnDimension('A')->setWidth(20);
$ws->getColumnDimension('C')->setWidth(20);

/* ========== HOJA 5: SCRIPT DE VENTA ========== */
$s->createSheet()->setTitle('Script de Venta');
$ws = $s->getSheet(4);

$ws->mergeCells('A1:B1');
$ws->setCellValue('A1', '📞 SCRIPT DE VENTA + OBJECIONES');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($TEAL);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$script = [
    ['APERTURA', ''],
    ['Saludo inicial', '"Hola Dr./Dra. [Nombre], soy [Tu nombre] de DocFácil. Te contacto porque ayudamos a doctores como tú a organizar su consultorio, recordar citas por WhatsApp automático y ahorrar 2 horas al día. ¿Tienes 3 minutos?"'],
    ['', ''],
    ['DOLOR', ''],
    ['Pregunta clave', '"Doctor, ¿cuánto tiempo al día pasas organizando citas en libreta o Excel?"'],
    ['Pregunta 2', '"¿Cuántos pacientes se te van porque no se enteran que tienen cita?"'],
    ['Pregunta 3', '"¿Cobras efectivo o pierdes ventas porque el paciente no trae dinero?"'],
    ['', ''],
    ['DEMO', ''],
    ['Punto 1', 'Muestra AGENDA: "Aquí ves tu agenda del mes, arrastras citas para reagendar, bloqueas horarios con un clic."'],
    ['Punto 2', 'Muestra RECORDATORIOS: "Cada cita genera recordatorio automático 24h y 2h antes por WhatsApp."'],
    ['Punto 3', 'Muestra RECETAS PDF: "Al terminar la consulta, un clic y se genera receta con tu cédula, membrete y firma."'],
    ['Punto 4', 'Muestra COBRO WhatsApp: "Al terminar la consulta, un clic y el paciente recibe el cobro."'],
    ['Punto 5', 'Muestra ODONTOGRAMA/EXPEDIENTE: "Todo el historial clínico del paciente en una pantalla, con alertas de alergias y antecedentes."'],
    ['', ''],
    ['CIERRE', ''],
    ['Frase de cierre', '"Doctor, por $299 al mes ahorras 2 horas al día y recuperas 8 pacientes que antes no llegaban = $4,800 extra al mes. DocFácil se paga 16 veces."'],
    ['Call to action', '"Te doy 14 días gratis con todas las features. Sin tarjeta. ¿A qué hora te puedo ayudar a configurarlo?"'],
    ['', ''],
    ['OBJECIONES', ''],
    ['"Es caro"', '"Doctor, ¿cuánto cobras por consulta? ¿$500? DocFácil Pro cuesta MENOS de 1 consulta al mes y te ahorra 8 pacientes en recordatorios WhatsApp. Se paga 16 veces."'],
    ['"No soy tecnológico"', '"Por eso lo diseñamos simple. Si sabes usar WhatsApp, sabes usar DocFácil. Te acompañamos en la configuración inicial sin costo."'],
    ['"Ya tengo software"', '"¿Es en la nube o instalado en una sola compu? ¿Manda recordatorios WhatsApp automáticos? ¿Tiene portal del paciente? Pruébalo 14 días gratis sin cancelar lo actual."'],
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

/* ========== HOJA 6: CALCULADORA ROI ========== */
$s->createSheet()->setTitle('Calculadora ROI');
$ws = $s->getSheet(5);

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

$ws->setCellValue('A11', 'TOTAL AHORRO/MES');
$ws->setCellValue('B11', '=B9+B10');
$ws->setCellValue('C11', 'Total mensual ganado con DocFácil');
$ws->getStyle('A11:C11')->getFont()->setBold(true)->setSize(14);
$ws->getStyle('A11:C11')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($LIGHT_BG);

$ws->getStyle('B9:B11')->getNumberFormat()->setFormatCode('"$"#,##0');
$ws->getStyle('A9:C11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// ROI
$ws->setCellValue('A13', 'Precio DocFácil Pro');
$ws->setCellValue('B13', 299);
$ws->getStyle('B13')->getNumberFormat()->setFormatCode('"$"#,##0');

$ws->setCellValue('A14', 'ROI (cuántas veces paga DocFácil)');
$ws->setCellValue('B14', '=B11/B13');
$ws->getStyle('B14')->getNumberFormat()->setFormatCode('0.0"x"');
$ws->getStyle('A14:B14')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB($TEAL);

$ws->getColumnDimension('A')->setWidth(40);
$ws->getColumnDimension('B')->setWidth(18);
$ws->getColumnDimension('C')->setWidth(45);

/* ========== HOJA 7: COMISIONES ========== */
$s->createSheet()->setTitle('Comisiones');
$ws = $s->getSheet(6);

$ws->mergeCells('A1:D1');
$ws->setCellValue('A1', '💰 ESQUEMA DE COMISIONES');
$ws->getStyle('A1')->getFont()->setBold(true)->setSize(20)->getColor()->setRGB('FFFFFF');
$ws->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($GOLD);
$ws->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$ws->getRowDimension(1)->setRowHeight(40);

$ws->mergeCells('A3:D3');
$ws->setCellValue('A3', '🎯 GANAS 1.5 MENSUALIDADES POR CADA CLIENTE DE PAGO (Básico, Pro o Clínica)');
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
    ['Básico', '$149', '$223.50', '$111.75 + $111.75'],
    ['Pro ⭐', '$299', '$448.50', '$224.25 + $224.25'],
    ['Clínica', '$499', '$748.50', '$374.25 + $374.25'],
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
    ['Starter', '3 Básico + 2 Pro', '3x$223.50 + 2x$448.50', '$1,567.50'],
    ['Medium', '5 Pro + 1 Clínica', '5x$448.50 + 1x$748.50', '$2,991'],
    ['Top', '10 Pro + 2 Clínica', '10x$448.50 + 2x$748.50', '$5,982'],
    ['Elite', '5 Básico + 10 Pro + 3 Clínica', 'Mix completo', '$7,843.50'],
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

/* ========== HOJA 8: LINKS Y DEMOS ========== */
$s->createSheet()->setTitle('Links y Recursos');
$ws = $s->getSheet(7);

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

/* ========== HOJA 9: CHECKLIST DE VENTA ========== */
$s->createSheet()->setTitle('Checklist');
$ws = $s->getSheet(8);

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
    ['☐', 'Demo en vivo', 'Mostrar agenda + recordatorios WhatsApp + recetas PDF + cobro por WhatsApp'],
    ['☐', 'Cotización', 'Mostrar Plan Pro $299 con 50% descuento = $149 de por vida (fundador)'],
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
echo "📊 9 hojas con toda la información para cerrar ventas\n";
echo "🚀 Listo para compartir con vendedores\n";
