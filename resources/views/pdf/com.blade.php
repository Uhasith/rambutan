<style>
    /* .passbook-details{
        margin-bottom: 35px;
        float: right;
        font-size: 12px;
    }
    table{
        width: 100%;
    }
    table.transections td{
        padding-bottom: 5px;
        font-size: 9px;
    }
    table.transections td.date{
        width: 20px;
    }
    table.passbook-details td{
        width: 25%;
    } */
    table.transections{
        font-size: 9px;
        padding-left: 3.5cm;
        padding-right: 3.5cm;
    }
    table.transections td.id{
        padding-left: 0.5cm;
        padding-right: 0.5cm;
    }
    table.transections td.date{
        padding-right: 0.5cm;
    }
    table.transections td.type{
        padding-right: 0.5cm;
    }
    table.transections td.amount{
        padding-right: 0.5cm;
        text-align: right;
        /* width: 2.5cm; */
    }
    table.transections td.withdraw{
        padding-right: 0.5cm;
        text-align: right;
        /* width: 2.5cm; */
    }
    table.transections td.balence{
        padding-right: 0.5cm;
        text-align: right;
        /* width: 3cm; */
    }
</style>
<div class="pdf-view">

    <!-- <table class="passbook-details">
        <tr class="customer-details">
            <td>
                <span>{{$customer['name']}}</span><br>
                <span>{{$customer['address']}}</span><br>
                <span>{{$customer['city']}}</span><br>
                <span>{{$customer['address_line_1']}}</span>
            </td>
            <td></td>
            <td></td>
            <td>      
                <span></span>
                <span>{{$customer['account_number']}}</span><br>
                <span>LKR</span>
            </td>
        </tr>
    </table>
  -->

    <table class="transections">
        @foreach ($transactions as $item)
            {{json_encode($item)}}
            <tr>
                <td class="id">{{ $loop->iteration }}</td>
                <td class="date">{{$item['date']}}</td>
                <td class="type">{{$item['depositType']}}</td>
                <td class="amount">{{$item['depositAmount']}}</td>
                <td class="withdraw">{{$item['withdrawalAmount']}}</td>
                <td class="balence">{{$item['balance']}}</td>
            </tr>
        @endforeach
    </table>
    <html-separator/>

    <html-separator/>
</div>