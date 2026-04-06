<?php

namespace Database\Seeders;

use App\Models\Prospect;
use Illuminate\Database\Seeder;

class ProspectSeeder extends Seeder
{
    public function run(): void
    {
        $prospects = [
            // ╔══════════════════════════════════════════════════════╗
            // ║  CON EMAIL VERIFICADO (sitios web propios)          ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dra. Susana Pedrero de la Cruz', 'email' => 'spedrero@hotmail.com', 'phone' => '6699818221', 'clinic_name' => 'Clínica Dental Dra. Pedrero', 'city' => 'Mazatlán', 'specialty' => 'Ortodoncia', 'address' => 'José María Canizales 509-1, Centro'],
            ['name' => 'Dra. Lourdes López López', 'email' => 'dra.lourdeslopez@hotmail.com', 'phone' => '6677162345', 'clinic_name' => 'Ginecología Dra. López', 'city' => 'Culiacán', 'specialty' => 'Ginecología', 'address' => 'Aldama 171, Miguel Alemán'],
            ['name' => 'Dra. Ariana Moreno Zavala', 'email' => 'arymoreno0@gmail.com', 'phone' => '6673893571', 'clinic_name' => 'Cirugía Maxilofacial Dra. Moreno', 'city' => 'Culiacán', 'specialty' => 'Cirugía Maxilofacial', 'address' => 'Eucaliptos 1701, La Campiña'],
            ['name' => 'Dr. Héctor Valenzuela', 'email' => 'cardiologiadrvalenzuela@gmail.com', 'phone' => '6671058664', 'clinic_name' => 'Centro Cardiológico Colinas', 'city' => 'Culiacán', 'specialty' => 'Cardiología', 'address' => 'Cerro de las 7 Gotas 1905, Colinas de San Miguel'],
            ['name' => 'Dr. Osmany Salomón Hernández', 'email' => 'dr.salomonh@hotmail.com', 'phone' => '6671893056', 'clinic_name' => 'Psiquiatría Dr. Salomón', 'city' => 'Culiacán', 'specialty' => 'Psiquiatría', 'address' => 'Josefa Ortiz de Domínguez 1555, Gabriel Leyva'],
            ['name' => 'Dental Empresarial', 'email' => 'dentalempresarial@hotmail.com', 'phone' => '6688124614', 'clinic_name' => 'Dental Empresarial', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Ignacio Allende 655 Sur Local-B, Centro'],
            ['name' => 'Dr. Mario K. Uehara', 'email' => 'dentarte.uehara@hotmail.com', 'phone' => '6688123822', 'clinic_name' => 'Dentarte Uehara', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Francisco Javier Mina 215 Nte, Centro'],
            ['name' => 'Dra. Griselda Castro Chávez', 'email' => 'bettyboo_355@hotmail.com', 'phone' => '6688152727', 'clinic_name' => 'Consultorio Dental Castro', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Gabriel Leyva 13, Centro'],
            ['name' => 'Dra. Fátima Tejeda Campas', 'email' => 'dratejedaortodoncia@gmail.com', 'phone' => '6688129508', 'clinic_name' => 'Ortodoncia Dra. Tejeda', 'city' => 'Los Mochis', 'specialty' => 'Ortodoncia', 'address' => 'Álvaro Obregón 1431, Jardines del Sol'],
            ['name' => 'Dra. Ma. Angélica Núñez Núñez', 'email' => 'dra_angelica_n@hotmail.com', 'phone' => '6677159381', 'clinic_name' => 'Implantes y Periodoncia', 'city' => 'Culiacán', 'specialty' => 'Periodoncia', 'address' => 'Dr. Mora 1549-1, Las Quintas'],
            ['name' => 'Dr. Francisco Quintero', 'email' => 'drfranciscoquintero@hotmail.com', 'phone' => null, 'clinic_name' => 'Consultorio Dr. Quintero', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => null],
            ['name' => 'Dr. Sergio Acoltzi Escorcia', 'email' => 'sergiouae@hotmail.com', 'phone' => '6691120100', 'clinic_name' => 'Gastroenterología Dr. Acoltzi', 'city' => 'Mazatlán', 'specialty' => 'Cirugía General', 'address' => 'Blvd. Marina Mazatlán 2207, Piso 1, Cons. 106'],
            ['name' => 'Consultorio Médico Dental', 'email' => 'endopaco@hotmail.com', 'phone' => null, 'clinic_name' => 'Consultorio Médico Dental', 'city' => 'Culiacán', 'specialty' => 'Endodoncia', 'address' => 'Av. Álvaro Obregón 1298, Guadalupe'],
            ['name' => 'Dr. Iván Meza Manjarrez', 'email' => null, 'phone' => '6676273844', 'clinic_name' => 'Prótesis e Implantes Dr. Meza', 'city' => 'Culiacán', 'specialty' => 'Implantología', 'address' => 'Juan M. Banderas 1255, Guadalupe'],
            ['name' => 'Iliana Aguilar Zazueta', 'email' => 'iliaguilarzazueta@hotmail.com', 'phone' => null, 'clinic_name' => 'Consultorio Dra. Aguilar', 'city' => 'Mazatlán', 'specialty' => 'Medicina General', 'address' => 'Av. Rafael Buelna 198, Cons. 501, Hacienda Las Cruces'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  SECCIÓN AMARILLA - CULIACÁN (teléfono verificado)  ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'My Dentist Consultorio Dental', 'email' => null, 'phone' => '6673169423', 'clinic_name' => 'My Dentist', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Álvaro Obregón 1215, Guadalupe'],
            ['name' => 'Dr. Gilberto Sarabia Mendoza', 'email' => null, 'phone' => '6677127107', 'clinic_name' => 'Consultorio Dr. Sarabia', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Francisco Villa Ote. 93, Centro'],
            ['name' => 'Dra. Libia Marisela Escudero García', 'email' => null, 'phone' => '6677662229', 'clinic_name' => 'Consultorio Dra. Escudero', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Pedro Infante 1617, Tres Ríos'],
            ['name' => 'Dra. Laura Lucía Acosta Kelly', 'email' => null, 'phone' => '6677159318', 'clinic_name' => 'Consultorio Dra. Acosta', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Nicolás Bravo 1633, Los Pinos'],
            ['name' => 'Dr. José Israel Aispuro Félix', 'email' => null, 'phone' => '6677123934', 'clinic_name' => 'Consultorio Dr. Aispuro', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Miguel Hidalgo 503, Centro'],
            ['name' => 'Dr. Juan Alberto Alarcón Martín', 'email' => null, 'phone' => '6677161177', 'clinic_name' => 'Grupo Médico del Humaya', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Escobedo 349, Centro'],
            ['name' => 'Asedent', 'email' => null, 'phone' => '6677160857', 'clinic_name' => 'Asedent', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Francisco I. Madero 290, Centro'],
            ['name' => 'Dr. Leonardo Baeza Aguilar', 'email' => null, 'phone' => '6677603674', 'clinic_name' => 'Consultorio Dr. Baeza', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Alcatraz 3764, Bugambilias'],
            ['name' => 'Dra. Kristell Bajo López', 'email' => null, 'phone' => '6677291054', 'clinic_name' => 'Consultorio Dra. Bajo', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. de las Américas 2575, Villa Universidad'],
            ['name' => 'Dr. Pastor Bojórquez Gaxiola', 'email' => null, 'phone' => '6677522299', 'clinic_name' => 'Consultorio Dr. Bojórquez', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Dr. Mora 1483, Las Quintas'],
            ['name' => 'Dra. Cintia Carranza López', 'email' => null, 'phone' => '6677500327', 'clinic_name' => 'Consultorio Dra. Carranza', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Diamantes 2891, Tulipanes'],
            ['name' => 'Dr. Javier Rubén Castaños Sosa', 'email' => null, 'phone' => '6677165400', 'clinic_name' => 'Consultorio Dr. Castaños', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Nevado de Toluca 1631, FITSE'],
            ['name' => 'Dr. Sergio Luis Castro Respaldo', 'email' => null, 'phone' => '6677123058', 'clinic_name' => 'Consultorio Dr. Castro', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Juan José Ríos 114, Jorge Almada'],
            ['name' => 'Dr. Javier Omar Cázares Zazueta', 'email' => null, 'phone' => '6677157980', 'clinic_name' => 'Consultorio Dr. Cázares', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Álvaro Obregón 2475, Centro'],
            ['name' => 'Dr. Carlos Cazárez Quiroz', 'email' => null, 'phone' => '6677139619', 'clinic_name' => 'Consultorio Dr. Cazárez', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Juan J. Ríos 162 Ote., Miguel Alemán'],
            ['name' => 'Centro de Especialidades Dentales', 'email' => null, 'phone' => '6677521548', 'clinic_name' => 'Centro de Especialidades Dentales', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Diego Valadez 164-203, Tres Ríos'],
            ['name' => 'Centro Dental Láser y Estética', 'email' => null, 'phone' => '6677165063', 'clinic_name' => 'Centro Dental Láser y Estética', 'city' => 'Culiacán', 'specialty' => 'Odontología Estética', 'address' => 'Insurgentes 879 Sur, Centro'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  SECCIÓN AMARILLA - MAZATLÁN (teléfono verificado)  ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dra. Kenia Danae López Osuna', 'email' => null, 'phone' => '6699839685', 'clinic_name' => 'Consultorio Dra. López', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Ciprés 809, Flamencos'],
            ['name' => 'Dra. Araceli Corral Pérez', 'email' => null, 'phone' => '6699903690', 'clinic_name' => 'Consultorio Dra. Corral', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Carr. Internacional al Norte 2001 L-6, Infonavit Playas'],
            ['name' => 'Odontología Integral Mazatlán', 'email' => null, 'phone' => '6699902300', 'clinic_name' => 'Odontología Integral', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Reforma 701, Flamencos'],
            ['name' => 'Childent', 'email' => null, 'phone' => '6691184133', 'clinic_name' => 'Childent', 'city' => 'Mazatlán', 'specialty' => 'Odontopediatría', 'address' => 'Río San Lorenzo 218 B, Palos Prietos'],
            ['name' => 'Dra. Yunuen Guzmán Ramírez', 'email' => null, 'phone' => '6699407177', 'clinic_name' => 'Consultorio Dra. Guzmán', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Bicentenario Juárez 123, Francisco Villa'],
            ['name' => 'Especialistas en Ortodoncia Mazatlán', 'email' => null, 'phone' => '6691937493', 'clinic_name' => 'Ortodoncia y Periodoncia', 'city' => 'Mazatlán', 'specialty' => 'Ortodoncia', 'address' => 'Coronel Medina 1023 Altos, Pueblo Nuevo'],
            ['name' => 'Ma. Magdalena Águila Vega', 'email' => null, 'phone' => '6699820272', 'clinic_name' => 'Consultorio Dra. Águila', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Miguel Hidalgo 29, Balcones de Loma Linda'],
            ['name' => 'Dra. María de Jesús Alba Loaiza', 'email' => null, 'phone' => '6699852803', 'clinic_name' => 'Consultorio Dra. Alba', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => '5 de Mayo 2306 Loc 3, Centro'],
            ['name' => 'Gloria del Carmen Alonso Paredes', 'email' => null, 'phone' => '6699840123', 'clinic_name' => 'Consultorio Dra. Alonso', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Sierra Nevada 114, Lomas de Mazatlán'],
            ['name' => 'Dr. David Arreola García', 'email' => null, 'phone' => '6699867001', 'clinic_name' => 'Consultorio Dr. Arreola', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Silverio Pérez 127, El Toreo'],
            ['name' => 'Dr. José Eduardo Audelo Aún', 'email' => null, 'phone' => '6699813841', 'clinic_name' => 'Consultorio Dr. Audelo', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Heriberto Frías 1509-11, Balcones de Loma Linda'],
            ['name' => 'Dr. Alfonso Benítez Magaña', 'email' => null, 'phone' => '6699166216', 'clinic_name' => 'Consultorio Dr. Benítez', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => '5 de Mayo 1620, Balcones de Loma Linda'],
            ['name' => 'Alberto Buelna Santos', 'email' => null, 'phone' => '6699842754', 'clinic_name' => 'Consultorio Buelna', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'De las Américas 28, Benito Juárez'],
            ['name' => 'Dolores Emilia Canedo Espinoza', 'email' => null, 'phone' => '6699834473', 'clinic_name' => 'Consultorio Canedo', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Tráfico 162, Urías'],
            ['name' => 'Ma. Teresa Cárdenas Saucedo', 'email' => null, 'phone' => '6699813626', 'clinic_name' => 'Consultorio Cárdenas', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Luis Zúñiga 415, Balcones de Loma Linda'],
            ['name' => 'Miguel Carrillo Gómez', 'email' => null, 'phone' => '6699815424', 'clinic_name' => 'Consultorio Carrillo', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Luis Zúñiga 707, Centro'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  SECCIÓN AMARILLA - LOS MOCHIS (teléfono verificado)║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dr. Manuel Francisco Flores Lerma', 'email' => null, 'phone' => '6688171842', 'clinic_name' => 'Consultorio Dr. Flores', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Blvd Zacatecas 668 Nte, Estrella'],
            ['name' => 'Dr. Juan Pablo Ríos Rosas', 'email' => null, 'phone' => '6681731839', 'clinic_name' => 'Consultorio Dr. Ríos', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Independencia 636-3, Centro'],
            ['name' => 'Integral Dent Los Mochis', 'email' => null, 'phone' => '6688159565', 'clinic_name' => 'Integral Dent', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Blvd Dren Juárez 150 Sur, Cuauhtémoc'],
            ['name' => 'Dr. Manuel Alba Mendiola', 'email' => null, 'phone' => '6688124245', 'clinic_name' => 'Consultorio Dr. Alba', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Benito Juárez Pte, Centro'],
            ['name' => 'Mi Salud Los Mochis', 'email' => null, 'phone' => '6688159696', 'clinic_name' => 'Mi Salud', 'city' => 'Los Mochis', 'specialty' => 'Medicina General', 'address' => 'Obregón 8, Centro'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DIRECTORIO NC MEXICO - LOS MOCHIS (con teléfono)  ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Diana Cristina Alvarado León', 'email' => null, 'phone' => '6688123310', 'clinic_name' => 'Consultorio Alvarado', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Blvd. Jiquilpan 615 Pte, Centro'],
            ['name' => 'Álvarez Dental', 'email' => null, 'phone' => '6683953442', 'clinic_name' => 'Álvarez Dental', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Blvd Zacatecas 876, Estrella'],
            ['name' => 'C.D. Valeria Trillo Medina', 'email' => null, 'phone' => '6681716634', 'clinic_name' => 'Dental Trillo', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Delicias C 1298, Las Delicias'],
            ['name' => 'Centro Odontológico Los Mochis', 'email' => null, 'phone' => '6681131081', 'clinic_name' => 'Centro Odontológico', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Blvd. Jiquilpan s/n, Jiquilpan'],
            ['name' => 'Clínica Dental Integral Delicias', 'email' => null, 'phone' => '6688122144', 'clinic_name' => 'Dental Integral Delicias', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Delicias C 1274, Las Delicias'],
            ['name' => 'Clínica Dental Novadent', 'email' => null, 'phone' => '6681771717', 'clinic_name' => 'Novadent', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Blvd. 10 de Mayo 453, Jiquilpan'],
            ['name' => 'Clínica Dental Rojas', 'email' => null, 'phone' => '6688124997', 'clinic_name' => 'Dental Rojas', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Gral. Macario Gaxiola 1704, Anáhuac'],
            ['name' => 'Clínica Dental Smile Center', 'email' => null, 'phone' => '6682248309', 'clinic_name' => 'Smile Center', 'city' => 'Los Mochis', 'specialty' => 'Odontología Estética', 'address' => 'Blvd. Justicia Social 926, Viñedos'],
            ['name' => 'Clínica Dental Unident', 'email' => null, 'phone' => '6688157032', 'clinic_name' => 'Unident Los Mochis', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Ángel Flores 52-C, Centro'],
            ['name' => 'Consultorio Dental Castro', 'email' => null, 'phone' => '6681198011', 'clinic_name' => 'Dental Castro', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Los Corrales 1826, Álamos Country'],
            ['name' => 'Dr. Plinio Leal Osuna', 'email' => null, 'phone' => '6688158937', 'clinic_name' => 'Consultorio Dr. Leal', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Rafael Buelna 800 A Pte, Sector Fátima'],
            ['name' => 'Dental Art Los Mochis', 'email' => null, 'phone' => '6688568024', 'clinic_name' => 'Dental Art', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Gral. Álvaro Obregón 81, Centro'],
            ['name' => 'Dental Flex Los Mochis', 'email' => null, 'phone' => '6681502345', 'clinic_name' => 'Dental Flex', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Av. Independencia 905 A'],
            ['name' => 'Centro de Medicina Hiperbárica', 'email' => null, 'phone' => '6688158025', 'clinic_name' => 'Centro Hiperbárica', 'city' => 'Los Mochis', 'specialty' => 'Medicina General', 'address' => '21 de Marzo 600 Pte, Jiquilpan'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - CULIACÁN DENTISTAS (nombre+dirección) ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dr. Joel A. Bracamontes Borrego', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Privado', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Cristóbal Colón 278 Ote, Centro'],
            ['name' => 'Dr. José Luis Castro Barrón', 'email' => null, 'phone' => null, 'clinic_name' => 'Select Dental', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Rolando Arjona Amabilis 1702'],
            ['name' => 'Dr. Luis Ángel Carranza Reséndez', 'email' => null, 'phone' => null, 'clinic_name' => 'Select Dental', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Rolando Arjona 1702'],
            ['name' => 'Dr. Emerik Alvarado Torres', 'email' => null, 'phone' => null, 'clinic_name' => 'Quality Dental', 'city' => 'Culiacán', 'specialty' => 'Odontología Estética', 'address' => 'Ruperto L. Paliza 170 Sur, Centro'],
            ['name' => 'Dr. Julio César Ley Quiñónez', 'email' => null, 'phone' => null, 'clinic_name' => 'Especialidades Dental Care', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Rafael Buelna Tenorio 373'],
            ['name' => 'Dra. Xitlalitl Esparza', 'email' => null, 'phone' => null, 'clinic_name' => 'Dra. Xitlalitl Esparza', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Dr. Mora 1573'],
            ['name' => 'Dra. Yubel Díaz Arredondo', 'email' => null, 'phone' => null, 'clinic_name' => 'Centro Médico Las Quintas', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Av. El Dorado 823'],
            ['name' => 'Dr. Pablo Guevara Conde', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Guevara', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Rolando Arjona 1702, Fracc. Azaleas'],
            ['name' => 'Dra. María Fernanda González Gastélum', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Dra. María Fernanda G.', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Mariano Escobedo 874A, Primer Cuadro'],
            ['name' => 'Dra. Ximena De León y Peña Simental', 'email' => null, 'phone' => null, 'clinic_name' => 'Wellness Médica', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Ciudades Hermanas 349'],
            ['name' => 'Dra. Alicia Korina Cañedo Flores', 'email' => null, 'phone' => null, 'clinic_name' => 'Smile Clinic', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => null],
            ['name' => 'Dr. Luis Ángel Castro González', 'email' => null, 'phone' => null, 'clinic_name' => 'Smile Clean', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Prol. Dr. Mora 1587-6'],
            ['name' => 'Dr. Roberto Carlos Santos Muñoz', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Privado', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Antonio Rosales 1085'],
            ['name' => 'Dr. Luis Guillermo Inzunza Castro', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Inzunza', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Rafael Buelna Tenorio 1144'],
            ['name' => 'Dr. Erasmo Yuriar', 'email' => null, 'phone' => null, 'clinic_name' => 'Adhestetic Dental Studio', 'city' => 'Culiacán', 'specialty' => 'Odontología Estética', 'address' => 'Virreyes 318-12'],
            ['name' => 'Dr. Miguel Ángel López Hernández', 'email' => null, 'phone' => null, 'clinic_name' => 'M IDental', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Estero Teacapan 3078'],
            ['name' => 'Dr. Salomón Sandoval Lara', 'email' => null, 'phone' => null, 'clinic_name' => 'Dentalis Culiacán', 'city' => 'Culiacán', 'specialty' => 'Odontopediatría', 'address' => 'Mariano Escobedo 844'],
            ['name' => 'Dr. Luis David Aragón Ramírez', 'email' => null, 'phone' => null, 'clinic_name' => 'Aradent', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Presa Adolfo López Mateos 905'],
            ['name' => 'Tanya Nereida Luna Félix', 'email' => null, 'phone' => null, 'clinic_name' => 'Luna Dental Especialidades', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Av. Guadalupe Victoria Nte. 609'],
            ['name' => 'Dra. Paulina Rodríguez', 'email' => null, 'phone' => null, 'clinic_name' => 'Estudio Clínico Estético', 'city' => 'Culiacán', 'specialty' => 'Odontología Estética', 'address' => 'Álvaro Obregón 1215'],
            ['name' => 'Dra. Gabriela Flores Islas', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Siglo XXI', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Prol. Álvaro Obregón 1429-5 Sur'],
            ['name' => 'Dra. Yaneth Flores Salazar', 'email' => null, 'phone' => null, 'clinic_name' => 'Odontopediatra Dra. Yaneth', 'city' => 'Culiacán', 'specialty' => 'Odontopediatría', 'address' => 'Xicoténcatl 1204, Las Quintas'],
            ['name' => 'Dr. Juan Roberto Inukai Sashida', 'email' => null, 'phone' => null, 'clinic_name' => 'Diseño de Sonrisa / Surgery Art', 'city' => 'Culiacán', 'specialty' => 'Odontología Estética', 'address' => 'Río Culiacán 230 Ote, Guadalupe'],
            ['name' => 'Dr. Luis Gerardo Urrea Salcedo', 'email' => null, 'phone' => null, 'clinic_name' => 'Smile Lab', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Colón 262, Primer Cuadro'],
            ['name' => 'Dra. Grecia Stefany Félix Nava', 'email' => null, 'phone' => null, 'clinic_name' => 'RenovaDent', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'República de Brasil 2372, Humaya'],
            ['name' => 'Dr. Kevin Antonio Castro Ayón', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Siglo XXI', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Prol. Álvaro Obregón 1429'],
            ['name' => 'Dr. Jesús Daniel Melchor Soto', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Privado', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Ángel Flores 353 Ote, Centro'],
            ['name' => 'Dra. Rebeca Anahí Hernández Medina', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Diamante', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Del Porvenir 3430'],
            ['name' => 'Dra. Beatriz Alatorre', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Privado', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Río Humaya 10 Pte Depto 3'],
            ['name' => 'Dr. Álvaro Sepúlveda Cebreros', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica Dental A&A', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Constituyente J. Natividad Macías'],
            ['name' => 'Dr. Walter Bernal Fonseca', 'email' => null, 'phone' => null, 'clinic_name' => 'Cirugía y Odontología Especializada', 'city' => 'Culiacán', 'specialty' => 'Cirugía Oral', 'address' => 'Escuadrón 201, 553, Jorge Almada'],
            ['name' => 'Dra. Sonya Piña', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Dra. Piña', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Mariano Escobedo 331 Pte, Centro'],
            ['name' => 'Dr. Omar Baltazar Barajas', 'email' => null, 'phone' => null, 'clinic_name' => 'Unidad Dental La Campiña', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Eucaliptos 1701-7'],
            ['name' => 'Dr. Kevin Morales', 'email' => null, 'phone' => null, 'clinic_name' => 'LabsEndo Endodoncia', 'city' => 'Culiacán', 'specialty' => 'Endodoncia', 'address' => 'Eclipse 3967, Prados de la Conquista'],
            ['name' => 'Dra. Kirenia Barraza Velarde', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Las Quintas', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Sinaloa 844'],
            ['name' => 'Dra. Mónica Torres Miranda', 'email' => null, 'phone' => null, 'clinic_name' => 'Family Dental Plus', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. Valle Alto 5193'],
            ['name' => 'Dra. Denisse Madrigal', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Dra. Madrigal', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Paseo Niños Héroes 640 Ote, Centro'],
            ['name' => 'Dr. Johnbob Benji GCH', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Solutions Culiacán', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Raúl Cervantes Ahumada 2904-A'],
            ['name' => 'Dra. Favia Picos Salazar', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica OriDental', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Cristóbal Colón 315A'],
            ['name' => 'Dr. Juan Carlos Coronel Zamudio', 'email' => null, 'phone' => null, 'clinic_name' => 'Elite Dent Clínica', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Blvd. El Dorado 1705'],
            ['name' => 'Dra. Sandra Jiménez Aguilar', 'email' => null, 'phone' => null, 'clinic_name' => 'Maja Spazio Dental', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Benito Juárez 426 Pte'],
            ['name' => 'Brianda S. Iribe Ramírez', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental IRIBE', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Enrique González 4308-B, Infonavit Barrancos'],
            ['name' => 'Dental Care Culiacán', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Care Culiacán', 'city' => 'Culiacán', 'specialty' => 'Odontología General', 'address' => 'Vialidad del Congreso 2565'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - MAZATLÁN DENTISTAS                    ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dr. Ricardo Ayón García', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Dr. Ayón', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Ahuizótl 919'],
            ['name' => 'Dr. Jesús Octavio Romero Águila', 'email' => null, 'phone' => null, 'clinic_name' => 'Rehabilitación Oral y Ortodoncia', 'city' => 'Mazatlán', 'specialty' => 'Ortodoncia', 'address' => 'Reforma 2007-B'],
            ['name' => 'Dra. Teresa Rodríguez Candia', 'email' => null, 'phone' => null, 'clinic_name' => 'Ortodoncia y Ortopedia', 'city' => 'Mazatlán', 'specialty' => 'Ortodoncia', 'address' => 'Sierra India 29, Lomas de Mazatlán'],
            ['name' => 'Dr. Luis G. Ibarra Capaceta', 'email' => null, 'phone' => null, 'clinic_name' => 'Ibadent', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'De La Amistad 1155'],
            ['name' => 'Dra. Gretel Padilla Vargas', 'email' => null, 'phone' => null, 'clinic_name' => 'Implantare Clínica Dental', 'city' => 'Mazatlán', 'specialty' => 'Implantología', 'address' => 'Carlos Canseco 6046, Local 12A'],
            ['name' => 'Dr. Gabriel Osuna Corona', 'email' => null, 'phone' => null, 'clinic_name' => 'Estética y Rehabilitación Dental', 'city' => 'Mazatlán', 'specialty' => 'Odontología Estética', 'address' => 'Revolución 622'],
            ['name' => 'Dra. Dulce María García Ochoa', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica Dental Pacifika', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Insurgentes 910'],
            ['name' => 'Dra. Sofía Valenzuela', 'email' => null, 'phone' => null, 'clinic_name' => 'Odontología Dra. Sofía', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'De las Torres 10007, La Joya'],
            ['name' => 'Dr. Airto Sánchez López', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Aesthetics', 'city' => 'Mazatlán', 'specialty' => 'Odontología Estética', 'address' => 'Olas Altas 66'],
            ['name' => 'Dr. Víctor Flores Enciso', 'email' => null, 'phone' => null, 'clinic_name' => 'Smile Dental Studio', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Hacienda del Seminario 5000, Local 17'],
            ['name' => 'Dra. Alejandra Corrales Peña', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica Dental Insurgentes', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Insurgentes 508'],
            ['name' => 'Dr. Luigi Bortolotti López', 'email' => null, 'phone' => null, 'clinic_name' => 'Dr. Luigi Bortolotti', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Paseo de las Torres 10019, La Joya'],
            ['name' => 'Dr. Juan Arnoldo León Garzón', 'email' => null, 'phone' => null, 'clinic_name' => 'ARVI Especialidades Dentales', 'city' => 'Mazatlán', 'specialty' => 'Odontología General', 'address' => 'Real Del Valle 3917'],
            ['name' => 'Dr. Francisco Valdez Oliver', 'email' => null, 'phone' => '6692611711', 'clinic_name' => 'Clínica Dental Especialidades', 'city' => 'Mazatlán', 'specialty' => 'Ortodoncia', 'address' => 'Tacuba 305-A, Playas del Sol'],
            ['name' => 'Dr. Jorge Alfonso Velarde', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental White Mazatlán', 'city' => 'Mazatlán', 'specialty' => 'Odontología Estética', 'address' => 'Prados del Sol 7900'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - LOS MOCHIS DENTISTAS                 ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dr. Miguel Ángel Ramírez Arenas', 'email' => null, 'phone' => null, 'clinic_name' => 'Odontología Ramírez', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Álvaro Obregón 1327, Jardines del Sol'],
            ['name' => 'Dr. Brian Román Gastélum', 'email' => null, 'phone' => null, 'clinic_name' => 'Dentina', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Heriberto Valdez 679'],
            ['name' => 'Dr. José Arturo Flores Espinosa', 'email' => null, 'phone' => null, 'clinic_name' => 'IMPLANMEDIC', 'city' => 'Los Mochis', 'specialty' => 'Implantología', 'address' => 'Antonio Rosales 134, Centro'],
            ['name' => 'Dra. Nancy Zavala Rodríguez', 'email' => null, 'phone' => null, 'clinic_name' => 'Dra. Nancy Zavala', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Blvd. Río Fuerte 184'],
            ['name' => 'Dr. Selman Orejel Gallardo', 'email' => null, 'phone' => null, 'clinic_name' => 'Cirugía Maxilofacial', 'city' => 'Los Mochis', 'specialty' => 'Cirugía Maxilofacial', 'address' => 'Venustiano Carranza 435'],
            ['name' => 'Dr. Jorge Iván Quiñónez', 'email' => null, 'phone' => null, 'clinic_name' => 'JQ Centro Dental', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Gabriel Leyva 38, Centro'],
            ['name' => 'Dr. Bernardo Villa Zavala', 'email' => null, 'phone' => null, 'clinic_name' => 'Villa Dental', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Obregón 908 Int. 3, Centro'],
            ['name' => 'Dra. Carla Hernández Guevara', 'email' => null, 'phone' => null, 'clinic_name' => 'Ortodoncia Dra. Hernández', 'city' => 'Los Mochis', 'specialty' => 'Ortodoncia', 'address' => 'Carranza 188 Pte'],
            ['name' => 'Dra. Sarahí Ahumada Leyva', 'email' => null, 'phone' => null, 'clinic_name' => 'Periodoncia e Implantología', 'city' => 'Los Mochis', 'specialty' => 'Periodoncia', 'address' => 'Serdán 236'],
            ['name' => 'Dr. Alberto Duarte', 'email' => null, 'phone' => null, 'clinic_name' => 'Dr. Alberto Duarte', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Zapata 220, entre Serdán y Rendón'],
            ['name' => 'Dra. Abigail Báez Bringas', 'email' => null, 'phone' => null, 'clinic_name' => 'Báez Clínica Dental', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Benito Juárez 412 Pte Int 5, Plaza Juárez'],
            ['name' => 'Dra. Kassandra Leyva', 'email' => null, 'phone' => null, 'clinic_name' => 'KLEY Clínica Dental', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Benito Juárez 142, Centro'],
            ['name' => 'Dra. Elisa Murrieta', 'email' => null, 'phone' => null, 'clinic_name' => 'Studio Dental Elisa Murrieta', 'city' => 'Los Mochis', 'specialty' => 'Odontología General', 'address' => 'Obregón 1088'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - GUASAVE DENTISTAS                     ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dr. Jorge Camacho Serna', 'email' => null, 'phone' => null, 'clinic_name' => 'Hospital Esmed', 'city' => 'Guasave', 'specialty' => 'Odontología General', 'address' => '5 de Febrero, Guasave'],
            ['name' => 'Dra. Petra Guadalupe Atondo', 'email' => null, 'phone' => null, 'clinic_name' => 'Odontología Petra', 'city' => 'Guasave', 'specialty' => 'Odontología General', 'address' => 'Antonio Norzagaray 219'],
            ['name' => 'Dr. Josefath Sandoval Castro', 'email' => null, 'phone' => null, 'clinic_name' => 'Implantdent Cirugía Oral', 'city' => 'Guasave', 'specialty' => 'Cirugía Oral', 'address' => 'Madero B198'],
            ['name' => 'Dr. Eduardo García Gámez', 'email' => null, 'phone' => null, 'clinic_name' => 'Dr. Eduardo García Consultorio', 'city' => 'Guasave', 'specialty' => 'Odontología General', 'address' => 'Blvd. Central 477'],
            ['name' => 'Dra. Amayrani Mora Valenzuela', 'email' => null, 'phone' => null, 'clinic_name' => 'Centro AMBERES', 'city' => 'Guasave', 'specialty' => 'Odontología General', 'address' => 'Blvd. 20 de Noviembre 459'],
            ['name' => 'Dra. Jipssy Norzagaray', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Norzagaray', 'city' => 'Guasave', 'specialty' => 'Odontología General', 'address' => 'Blvd. Insurgentes 233, Ejidal'],
            ['name' => 'Dra. Alejandra Hernández Rubio', 'email' => null, 'phone' => null, 'clinic_name' => 'Odontología Integral y Estética', 'city' => 'Guasave', 'specialty' => 'Odontología Estética', 'address' => 'Blvd. Felipe Ángeles 262'],
            ['name' => 'Dra. Alejandra Arce Lugo', 'email' => null, 'phone' => null, 'clinic_name' => 'Odontología Arce', 'city' => 'Guasave', 'specialty' => 'Odontología General', 'address' => 'Esq. Miguel Leyson Pérez y Fraternidad'],
            ['name' => 'Dra. Rozely Sánchez Gutiérrez', 'email' => null, 'phone' => null, 'clinic_name' => 'Dental Quality', 'city' => 'Guasave', 'specialty' => 'Odontología General', 'address' => 'Heriberto Valdez Romero 777'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - CULIACÁN MÉDICOS GENERALES            ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dr. Luis Eduardo Sevilla', 'email' => null, 'phone' => null, 'clinic_name' => 'Médica 901', 'city' => 'Culiacán', 'specialty' => 'Medicina General', 'address' => 'Presa del Humaya 901, Las Quintas'],
            ['name' => 'Dr. Jerson Félix Rubio', 'email' => null, 'phone' => null, 'clinic_name' => 'Hominis Neurociencias', 'city' => 'Culiacán', 'specialty' => 'Psiquiatría', 'address' => 'Miguel Hidalgo 340'],
            ['name' => 'Dr. José Julio Ramírez de la O', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica Lomas de Guadalupe', 'city' => 'Culiacán', 'specialty' => 'Medicina General', 'address' => 'Juan de Dios Bátiz 521'],
            ['name' => 'Dra. Ana Lucía Cota', 'email' => null, 'phone' => null, 'clinic_name' => 'Hayka Consultorio', 'city' => 'Culiacán', 'specialty' => 'Medicina General', 'address' => 'Blvd. Manuel Romero 131'],
            ['name' => 'Dr. Luis Daniel Duarte Salazar', 'email' => null, 'phone' => null, 'clinic_name' => 'LATIV Centro Médico', 'city' => 'Culiacán', 'specialty' => 'Medicina General', 'address' => 'Miguel Hidalgo 225'],
            ['name' => 'Dra. Jennifer Lerma López', 'email' => null, 'phone' => null, 'clinic_name' => 'Reumatología Dra. Lerma', 'city' => 'Culiacán', 'specialty' => 'Reumatología', 'address' => 'Gral. Ramón Corona 342'],
            ['name' => 'Dra. Blanca Xóchitl Núñez Millán', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica Integra Chapultepec', 'city' => 'Culiacán', 'specialty' => 'Medicina General', 'address' => 'Agricultores 396'],
            ['name' => 'Dr. Alexis Jacobo García', 'email' => null, 'phone' => null, 'clinic_name' => 'BioVein Clínica Venosa', 'city' => 'Culiacán', 'specialty' => 'Medicina General', 'address' => 'Miguel Hidalgo 879'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - CULIACÁN DERMATÓLOGOS                 ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dra. Rosal Valenzuela Marrufo', 'email' => null, 'phone' => null, 'clinic_name' => 'Torre Cemsi 2', 'city' => 'Culiacán', 'specialty' => 'Dermatología', 'address' => 'Miguel Hidalgo 340'],
            ['name' => 'Dra. Eloiza Ylé Arámburo', 'email' => null, 'phone' => null, 'clinic_name' => 'Médica Anaya', 'city' => 'Culiacán', 'specialty' => 'Dermatología', 'address' => 'Blvd. Pedro Anaya 1493'],
            ['name' => 'Dra. Valeria Álvarez Rivero', 'email' => null, 'phone' => null, 'clinic_name' => 'CLN Center Plastic Surgery', 'city' => 'Culiacán', 'specialty' => 'Dermatología', 'address' => 'Manuel Bonilla 1186'],
            ['name' => 'Dra. Mariana Rochín Tolosa', 'email' => null, 'phone' => null, 'clinic_name' => 'DERMAR Clínica Dermatológica', 'city' => 'Culiacán', 'specialty' => 'Dermatología', 'address' => 'Josefa Ortiz de Domínguez 598'],
            ['name' => 'Dra. Lorena Magallón Zazueta', 'email' => null, 'phone' => null, 'clinic_name' => 'Torre Cemsi 2', 'city' => 'Culiacán', 'specialty' => 'Dermatología', 'address' => 'Miguel Hidalgo 340'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - CULIACÁN PEDIATRAS                    ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dr. Octavio Herrera Zaragoza', 'email' => null, 'phone' => null, 'clinic_name' => 'Plaza del Bebé Torre Médica', 'city' => 'Culiacán', 'specialty' => 'Pediatría', 'address' => 'David Alfaro Siqueiros 362, Tres Ríos'],
            ['name' => 'Dra. Nery Amézquita', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica Integral Chapultepec', 'city' => 'Culiacán', 'specialty' => 'Pediatría', 'address' => 'Agricultores 396'],
            ['name' => 'Dra. Paulina Blanco Murillo', 'email' => null, 'phone' => null, 'clinic_name' => 'Médica Victoria Culiacán', 'city' => 'Culiacán', 'specialty' => 'Pediatría', 'address' => 'Río San Lorenzo 96'],
            ['name' => 'Dr. Miguel Ángel Garrido Rojo', 'email' => null, 'phone' => null, 'clinic_name' => 'Centro Médico América', 'city' => 'Culiacán', 'specialty' => 'Pediatría', 'address' => 'Jesús Andrade 354'],
            ['name' => 'Dra. Gabriela Inzunza Manjarrez', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Privado', 'city' => 'Culiacán', 'specialty' => 'Pediatría', 'address' => 'Calzada Ecuador 2157, Altamira'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - CULIACÁN GINECÓLOGOS                  ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dra. Arianna Rodríguez Aguilar', 'email' => null, 'phone' => null, 'clinic_name' => 'Torre CEMSI', 'city' => 'Culiacán', 'specialty' => 'Ginecología', 'address' => 'Miguel Hidalgo 302 Ote'],
            ['name' => 'Dr. Jesús Ramón Taboada Uriarte', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica Integral Chapultepec', 'city' => 'Culiacán', 'specialty' => 'Ginecología', 'address' => 'Pedro María Anaya y Agricultores 396'],
            ['name' => 'Dr. Miguel López Rioja', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica de Fertilidad ViaFERT', 'city' => 'Culiacán', 'specialty' => 'Ginecología', 'address' => 'Mariano Escobedo 185'],
            ['name' => 'Dr. Víctor Martínez Beltrán', 'email' => null, 'phone' => null, 'clinic_name' => 'BIOFATIMA Centro Médico', 'city' => 'Culiacán', 'specialty' => 'Ginecología', 'address' => 'Blvd. Pedro Anaya 2169'],
            ['name' => 'Dra. Mercedes García Verdugo', 'email' => null, 'phone' => null, 'clinic_name' => 'Torre Médica 901', 'city' => 'Culiacán', 'specialty' => 'Ginecología', 'address' => 'Presa Humaya 901'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  DOCTORALIA - MAZATLÁN MÉDICOS GENERALES            ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dra. Edith Noriega Torres', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Dra. Noriega', 'city' => 'Mazatlán', 'specialty' => 'Medicina General', 'address' => 'Turquesa 529'],
            ['name' => 'Dr. Humberto Rodríguez', 'email' => null, 'phone' => null, 'clinic_name' => 'Hospital Marina Mazatlán', 'city' => 'Mazatlán', 'specialty' => 'Medicina General', 'address' => 'Carlos Canseco 6048'],
            ['name' => 'Dra. Karime Cárdenas Ureña', 'email' => null, 'phone' => null, 'clinic_name' => 'Polimédica Mazatlán', 'city' => 'Mazatlán', 'specialty' => 'Medicina General', 'address' => 'Rafael Buelna 198, Hacienda Las Cruces'],
            ['name' => 'Clínica San Jorge', 'email' => null, 'phone' => null, 'clinic_name' => 'Clínica San Jorge', 'city' => 'Mazatlán', 'specialty' => 'Medicina General', 'address' => 'Manuel J. Clouthier 4455, Lomas del Ébano'],
            ['name' => 'Dr. Jorge Arriaga Lizárraga', 'email' => null, 'phone' => null, 'clinic_name' => 'Consultorio Dr. Arriaga', 'city' => 'Mazatlán', 'specialty' => 'Medicina General', 'address' => 'Camarón Sábalo 4480 Int 3'],

            // ╔══════════════════════════════════════════════════════╗
            // ║  CLÍNICA DENTAL ESPECIALIDADES - EL ROSARIO         ║
            // ╚══════════════════════════════════════════════════════╝

            ['name' => 'Dr. Roberto Valdez Oliver', 'email' => null, 'phone' => '6949512388', 'clinic_name' => 'Clínica Dental Especialidades', 'city' => 'El Rosario', 'specialty' => 'Rehabilitación Oral', 'address' => 'Dr. Julio Ríos Tirado 12, Centro'],
        ];

        $this->command->info('Insertando ' . count($prospects) . ' prospectos reales de Sinaloa...');

        $inserted = 0;
        foreach ($prospects as $prospect) {
            // Use name+city as unique key to avoid duplicates
            $existing = Prospect::where('name', $prospect['name'])
                ->where('city', $prospect['city'])
                ->first();

            if ($existing) {
                $existing->update(array_merge($prospect, [
                    'source' => 'prospecting',
                ]));
            } else {
                Prospect::create(array_merge($prospect, [
                    'source' => 'prospecting',
                    'status' => 'new',
                ]));
                $inserted++;
            }
        }

        $this->command->info("✓ {$inserted} prospectos nuevos insertados. Total en seeder: " . count($prospects));
    }
}
