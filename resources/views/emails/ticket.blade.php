<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ticket creado</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f1f5f9;
    font-family: Arial, Helvetica, sans-serif;
    color: #334155;
">

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color: #f1f5f9; padding: 40px 15px;">

        <tr>
            <td align="center">

                <!-- CONTENEDOR PRINCIPAL -->
                <table width="600" cellpadding="0" cellspacing="0" border="0"
                    style="
                           max-width: 600px;
                           width: 100%;
                           background-color: #ffffff;
                           border-radius: 10px;
                           overflow: hidden;
                       ">


                    <!-- ENCABEZADO -->
                    <!-- ENCABEZADO -->
                    <tr>
                        <td style="
        background-color: #1e293b;
        padding: 20px 30px;
        color: #ffffff;
    ">

                            <!-- TABLA DEL ENCABEZADO -->
                            <table width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0">

                                <tr>

                                    <!-- ========================= -->
                                    <!-- LOGO - LADO IZQUIERDO -->
                                    <!-- ========================= -->

                                    <td width="40%"
                                        valign="middle"
                                        style="padding-right: 20px;
                                            border-right: 1px solid rgba(255,255,255,0.4);">

                                        <img
                                            src="cid:logo-legumex"
                                            alt="LEGUMEX"
                                            width="170"
                                            style="
                                                display: block;
                                                width: 170px;
                                                max-width: 100%;
                                                height: auto;
                        ">

                                    </td>


                                    <!-- ========================= -->
                                    <!-- TEXTO - LADO DERECHO -->
                                    <!-- ========================= -->

                                    <td width="60%"
                                        valign="middle"
                                        style="padding-left: 25px;">

                                        <h2 style="
                                            margin: 0;
                                            font-size: 22px;
                                            line-height: 1.3;
                                            color: #ffffff;
                    ">
                                            Sistema de Tickets TIC
                                        </h2>

                                        <p style="
                                            margin: 7px 0 0;
                                            font-size: 13px;
                                            line-height: 1.5;
                                            color: #cbd5e1;
                    ">
                                            Gestión de soporte tecnológico
                                        </p>

                                    </td>

                                </tr>

                            </table>

                        </td>
                    </tr>


                    <!-- CONTENIDO -->
                    <tr>
                        <td style="padding: 35px 30px;">

                            <!-- TÍTULO -->
                            <h1 style="
                                margin: 0 0 10px;
                                font-size: 24px;
                                color: #0f172a;
                            ">
                                Nuevo ticket creado
                            </h1>

                            <p style="
                                margin: 0 0 25px;
                                color: #64748b;
                                font-size: 15px;
                            ">
                                Se ha registrado correctamente un nuevo
                                ticket en el sistema.
                            </p>


                            <!-- USUARIO -->
                            <h3 style="
                                margin: 30px 0 10px;
                                font-size: 16px;
                                color: #0f172a;
                            ">
                                Información del usuario
                            </h3>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>

                                    <td style="
                                        background-color: #f8fafc;
                                        border: 1px solid #e2e8f0;
                                        padding: 15px;
                                    ">

                                        <span style="
                                            display: block;
                                            font-size: 12px;
                                            color: #64748b;
                                        ">
                                            CREADO POR
                                        </span>

                                        <strong style="
                                            display: block;
                                            margin-top: 5px;
                                            color: #334155;
                                        ">
                                            {{ $ticket->user?->name ?? 'Usuario no disponible' }}
                                        </strong>

                                    </td>

                                </tr>
                            </table>


                            <!-- INFORMACIÓN DEL TICKET -->
                            <h3 style="
                                margin: 30px 0 10px;
                                font-size: 16px;
                                color: #0f172a;
                            ">
                                Información del ticket
                            </h3>

                            <table width="100%" cellpadding="0" cellspacing="0">

                                <!-- TÍTULO -->
                                <tr>
                                    <td style="
                                        padding: 12px 0;
                                        border-bottom: 1px solid #e2e8f0;
                                    ">

                                        <span style="
                                            font-size: 12px;
                                            color: #64748b;
                                        ">
                                            TÍTULO
                                        </span>

                                        <div style="
                                            margin-top: 5px;
                                            font-size: 15px;
                                            color: #334155;
                                        ">
                                            {{ $ticket->title }}
                                        </div>

                                    </td>
                                </tr>

                                <!-- PRIORIDAD -->
                                <tr>
                                    <td style="padding: 12px 0;
                                        border-bottom: 1px solid #e2e8f0;">

                                        <span style="font-size: 12px;
                                            color: #64748b;">
                                            PRIORIDAD
                                        </span>

                                        <div style="margin-top: 5px;
                                            font-size: 15px;
                                            color: #334155;
                                            font-weight: bold;">
                                            {{ $ticket->priority->label() }}
                                        </div>

                                    </td>
                                </tr>


                                <!-- DESCRIPCIÓN -->
                                <tr>
                                    <td style="
                                        padding: 15px 0;
                                    ">

                                        <span style="
                                            font-size: 12px;
                                            color: #64748b;
                                        ">
                                            DESCRIPCIÓN
                                        </span>

                                        <div style="
                                            margin-top: 8px;
                                            padding: 15px;
                                            background-color: #f8fafc;
                                            border-left: 4px solid #2563eb;
                                            font-size: 14px;
                                            line-height: 1.6;
                                            color: #475569;
                                        ">
                                            {{ $ticket->description }}
                                        </div>

                                    </td>
                                </tr>


                                <!-- FECHA -->
                                <tr>
                                    <td style="
                                        padding: 10px 0;
                                    ">

                                        <span style="
                                            font-size: 12px;
                                            color: #64748b;
                                        ">
                                            FECHA DE CREACIÓN
                                        </span>

                                        <div style="
                                            margin-top: 5px;
                                            font-size: 14px;
                                            color: #334155;
                                        ">
                                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                                        </div>

                                    </td>
                                </tr>

                            </table>


                            <!-- MENSAJE -->
                            <div style="
                                margin-top: 25px;
                                padding: 15px;
                                background-color: #eff6ff;
                                border-radius: 6px;
                                color: #1e40af;
                                font-size: 13px;
                                line-height: 1.5;
                            ">

                                Tu solicitud ha sido registrada correctamente.
                                El equipo de soporte TIC dará seguimiento al ticket.

                            </div>

                        </td>
                    </tr>


                    <!-- FOOTER -->
                    <tr>
                        <td style="
                            background-color: #f8fafc;
                            border-top: 1px solid #e2e8f0;
                            padding: 20px 30px;
                            text-align: center;
                        ">

                            <p style="
                                margin: 0;
                                font-size: 12px;
                                color: #64748b;
                            ">
                                Este correo fue generado automáticamente.
                            </p>

                            <p style="
                                margin: 6px 0 0;
                                font-size: 12px;
                                color: #94a3b8;
                            ">
                                Sistema de Tickets TIC
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>

    </table>

</body>

</html>