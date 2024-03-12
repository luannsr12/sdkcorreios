<?php

namespace Sdkcorreios\Config;

class Status
{
    public const STATUS_DELIVERED = "DELIVERED";

    public const STATUS_MOVEMENT = "MOVEMENT";

    public const STATUS_NOTFOUND = "NOTFOUND";

    public const STATUS_NOBODYHOME = "NOBODY_HOME";

    public const STATUS_DELIVERY_FAILURE = "DELIVERY_FAILURE";

    public const STATUS_REFUSED_RECEIVE = "DELIVERY_FAILURE";

    public const STATUS_UNKNOWN_CUSTOMER = "UNKNOWN_CUSTOMER";

    public const STATUS_CUSTOMER_MOVED = "CUSTOMER_MOVED";

    public const STATUS_NO_IDENTIFICATION = "NO_IDENTIFICATION";

    public const STATUS_NEW_TRY = "NEW_TRY";

    public const STATUS_RETURN_SENDER = "RETURN_SENDER";

    public const STATUS_WAITING_WITHDRAWAL = "WAITING_WITHDRAWAL";

    public const STATUS_LATE = "LATE";

    public const STATUS_RETURN = "RETURN";

    public const STATUS_MAILBOX = "MAILBOX";

    public const STATUS_LOST = "LOST";

    public const STATUS_POSTED = "POSTED";

    public const STATUS_DISTRUBTION = "DISTRUBTION";

    public const STATUS_RECEIVED_BRAZIL = "RECEIVED_BRAZIL";

    public const STATUS_STOLEN = "STOLEN";

    public const STATUS_OUT_DELIVERY = "OUT_DELIVERY";


    public static function getStatus($string)
    {

        $status = "";

        if ($string == "") {
            $status = Status::STATUS_NOTFOUND;
        }

        $string = trim($string);

        switch ($string) {
            case 'Objeto entregue ao destinatário':
                $status = Status::STATUS_DELIVERED;
                break;

            case 'Entrega Efetuada':
                $status = Status::STATUS_DELIVERED;
                break;

            case 'A entrega não pode ser efetuada - Carteiro não atendido':
                $status = Status::STATUS_NOBODYHOME;
                break;

            case 'A entrega não pode ser efetuada':
                $status = Status::STATUS_DELIVERY_FAILURE;
                break;

            case 'A entrega não pode ser efetuada - Cliente recusou-se a receber':
                $status = Status::STATUS_REFUSED_RECEIVE;
                break;

            case 'A entrega não pode ser efetuada - Cliente desconhecido no local':
                $status = Status::STATUS_UNKNOWN_CUSTOMER;
                break;

            case 'A entrega não pode ser efetuada - Cliente mudou-se':
                $status = Status::STATUS_CUSTOMER_MOVED;
                break;

            case 'A entrega não pode ser efetuada - Destinatário não apresentou documento exigido':
                $status = Status::STATUS_NO_IDENTIFICATION;
                break;

            case 'Coleta ou entrega de objeto não efetuada':
                $status = Status::STATUS_NEW_TRY;
                break;

            case 'Destinatário não retirou objeto na Unidade dos Correios':
                $status = Status::STATUS_RETURN_SENDER;
                break;
            case 'Objeto aguardando retirada no endereço indicado':
                $status = Status::STATUS_WAITING_WITHDRAWAL;
                break;

            case 'Objeto com atraso na entrega':
                $status = Status::STATUS_LATE;
                break;

            case 'Objeto devolvido ao remetente':
                $status = Status::STATUS_RETURN;
                break;

            case 'Objeto disponível para retirada em Caixa Postal':
                $status = Status::STATUS_MAILBOX;
                break;

            case 'Objeto não localizado':
                $status = Status::STATUS_NOTFOUND;
                break;

            case 'Objeto Extraviado':
                $status = Status::STATUS_LOST;
                break;

            case 'Objeto postado':
                $status = Status::STATUS_POSTED;
                break;

            case 'Objeto postado após o horário limite da agência':
                $status = Status::STATUS_POSTED;
                break;

            case 'Objeto recebido na unidade de distribuição':
                $status = Status::STATUS_DISTRUBTION;
                break;

            case 'Objeto recebido no Brasil':
                $status = Status::STATUS_RECEIVED_BRAZIL;
                break;

            case 'Objeto roubado':
                $status = Status::STATUS_STOLEN;
                break;

            case 'Objeto encaminhado':
                $status = Status::STATUS_MOVEMENT;
                break;

            case 'Objeto saiu para entrega ao remetente':
                $status = Status::STATUS_OUT_DELIVERY;
                break;

            case 'Objeto saiu para entrega ao destinatário':
                $status = Status::STATUS_OUT_DELIVERY;
                break;

            case 'Tentativa de entrega não efetuada':
                $status = Status::STATUS_NEW_TRY;
                break;

            case 'Objeto em transferência - por favor aguarde':
                $status = Status::STATUS_MOVEMENT;
                break;
                
            case 'Em trânsito para Unidade de Distribuição':
                $status = Status::STATUS_MOVEMENT;
                break;

            default:
                $status = $string;
                break;
        }

        return $status;

    }

}
