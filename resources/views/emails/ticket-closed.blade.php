<!DOCTYPE html>

<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Ticket cerrado</title>

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
                <tr>
                    <td style="
                        background-color: #1e293b;
                        padding: 20px 30px;
                        color: #ffffff;
                    ">

                        <table width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0">

                            <tr>

                                <!-- LOGO -->
                                <td width="40%"
                                    valign="middle"
                                    style="
                                        padding-right: 20px;
                                        border-right: 1px solid rgba(255,255,255,0.4);
                                    ">

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

                                <!-- TEXTO -->
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
                            Ticket cerrado
                        </h1>

                        <p style="
                            margin: 0 0 25px;
                            color: #64748b;
                            font-size: 15px;
                            line-height: 1.5;
                        ">
                            Te informamos que tu solicitud de soporte ha sido
                            atendida y el ticket ha sido marcado como
                            <strong>cerrado</strong>.
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
                                        SOLICITANTE
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

                            <!-- ID -->
                            <tr>
                                <td style="
                                    padding: 12px 0;
                                    border-bottom: 1px solid #e2e8f0;
                                ">

                                    <span style="
                                        font-size: 12px;
                                        color: #64748b;
                                    ">
                                        TICKET
                                    </span>

                                    <div style="
                                        margin-top: 5px;
                                        font-size: 15px;
                                        color: #334155;
                                    ">
                                        #{{ $ticket->id }}
                                    </div>

                                </td>
                            </tr>


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
                                        border-left: 4px solid #16a34a;
                                        font-size: 14px;
                                        line-height: 1.6;
                                        color: #475569;
                                    ">
                                        {{ $ticket->description }}
                                    </div>

                                </td>
                            </tr>


                            <!-- CERRADO POR -->
                            <tr>
                                <td style="
                                    padding: 10px 0;
                                ">

                                    <span style="
                                        font-size: 12px;
                                        color: #64748b;
                                    ">
                                        CERRADO POR
                                    </span>

                                    <div style="
                                        margin-top: 5px;
                                        font-size: 14px;
                                        color: #334155;
                                    ">
                                        {{ $ticket->closedBy?->name ?? 'Usuario no disponible' }}
                                    </div>

                                </td>
                            </tr>


                            <!-- FECHA DE CIERRE -->
                            <tr>
                                <td style="
                                    padding: 10px 0;
                                ">

                                    <span style="
                                        font-size: 12px;
                                        color: #64748b;
                                    ">
                                        FECHA DE CIERRE
                                    </span>

                                    <div style="
                                        margin-top: 5px;
                                        font-size: 14px;
                                        color: #334155;
                                    ">
                                        {{ $ticket->closed_at?->format('d/m/Y H:i') ?? 'Fecha no disponible' }}
                                    </div>

                                </td>
                            </tr>

                        </table>


                        <!-- ESTADO -->
                        <div style="
                            margin-top: 25px;
                            padding: 15px;
                            background-color: #f0fdf4;
                            border-radius: 6px;
                            color: #166534;
                            font-size: 13px;
                            line-height: 1.5;
                            border: 1px solid #bbf7d0;
                        ">

                            <strong>Ticket cerrado correctamente.</strong>

                            <br><br>

                            La solicitud ha sido atendida por el equipo de
                            soporte TIC.

                        </div>


                        <!-- MENSAJE FINAL -->
                        <p style="
                            margin-top: 25px;
                            color: #64748b;
                            font-size: 13px;
                            line-height: 1.6;
                        ">
                            Si consideras que el problema no fue resuelto o
                            necesitas asistencia adicional, puedes comunicarte
                            nuevamente con el departamento de TIC.
                        </p>

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
