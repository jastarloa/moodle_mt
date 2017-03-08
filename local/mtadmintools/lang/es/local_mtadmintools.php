<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'local_mtadmintools', language 'es'
 *
 * @package   local_mtadmintools
 * @copyright  2017
 * @autor   Manu Peño
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Administración multi-cliente';

$string['mtadmintools:manageclientsettings'] = "Gestionar configuración de la cuenta de cliente";
$string['mtadmintools:readclientsettings'] = "Leer los datos de configuración de la cuenta de cliente";
$string['calcbilltask'] = "Moodle mt - Calcula factura mensual del tenant";
$string['cleantemptask'] = "Moodle mt - Limpia ficheros temporales de informes";

$string['mtclientzone'] = 'Zona de cliente';
$string['mtclientaccsettings'] = 'Configuración de zona de cliente';
$string['mtadminmngmnt'] = 'Gestión multi tenant';

$string['tenant'] = 'Tenant';
$string['selectdbtenant'] = 'Seleccione uno o más tenants';

$string['clients'] = 'Clientes';
$string['clientsettingssection'] = 'Configuración de la cuenta';
$string['adminmngmntsection'] = 'Admin management';
$string['billinginfo'] = 'Datos de facturación';
$string['billing'] = 'Facturación';
$string['billinghist'] = 'Histórico de cargos';
$string['monthbalance'] = 'Balance del mes, hasta el {$a}';
$string['clientconsumsection'] = 'Datos de consumo';
$string['tenantdefbackupzone'] = 'Zona de backups';
$string['forcecourse'] = 'Forzar a zona de curso';
$string['moodledef'] = 'La definida por Moodle';
$string['contact_data'] = 'Datos de contacto';
$string['extrasettings'] = 'Configuraciones extra';
$string['update'] = 'Actualizar';
$string['contactemailconfirm'] = 'Confirme el email';
$string['contactemailconfirm_err'] = 'El email de confirmación no coincide';
$string['state'] = 'Estado/Provincia';
$string['servicetitle'] = 'Título del servicio';
$string['servicetitle_help'] = 'Nombre del servicio que aparecerá como emisor de las facturas';
$string['tenant_pricing'] = 'Tarifas y precios para el cliente';
$string['currency'] = 'Moneda';
$string['disk_in_gb'] = 'Consumo de diso (en GB)';
$string['disk_cost'] = 'Precio por GB de disco consumido';
$string['disk_cost_help'] = 'Se tendrán en cuenta los archivos e imágenes del cliente (backups almacenados, imágenes ' .
    'adjuntadas en los editores de textos, documentos subidos a los cursos, etc.). Por favor, use punto como ' .
    'separador de decimales.';
$string['cost_by_user'] = 'Precio por usuario activo';
$string['cost_by_user_help'] = 'Un usuario activo será aquél que haya hecho login en la aplicacón a lo largo del mes';

$string['component'] = 'Componente';
$string['mimetype'] = 'Tipo mime';
$string['timecreated'] = 'Fecha de creación';
$string['contextlevel'] = 'Contexto';

$string['chart_disk_title'] = 'Consumo de disco';
$string['chart_active_users_title'] = 'Usuarios';
$string['total-usrs'] = 'Usuarios totales';
$string['month-actives'] = 'Usuarios activos durante el mes';
$string['chart_history_title'] = 'Histórico de consumo (últimos {$a} meses)';
$string['head-title-area'] = 'Área';
$string['head-title-bytes'] = 'Bytes';
$string['charge'] = 'Cargo';

$string['monthbill_subjetc'] = '{$a->servicetitle} Estado de Factura Disponible';
$string['monthbill_body'] = 'Saludos desde {$a->servicetitle},

Este e-mail confirma que está disponible el estado de su factura del último mes.
Se cargará a su cuenta la siguiente cantidad:

Total: {$a->bill}

Puede ver más detalles en su área de cliente ({$a->url}) o preguntando a {$a->supportemail}

Gracias por usar {$a->servicetitle}.

Atentamente,

{$a->servicetitle}';