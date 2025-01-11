<style>
    .passbook-details{
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
    }
</style>
<div class="pdf-view">

    <table class="passbook-details">
        <tr class="customer-details">
            <td></td>
            <td></td>
            <td></td>
            <td>
                <span>{{$customer['name']}}</span><br>
                <span>{{$customer['address']}}</span><br>
                <span>{{$customer['address_line_1']}}</span>
                <span>{{$customer['city']}}</span><br>
                <span>{{$customer['account_number']}}</span>
            </td>
        </tr>
    </table>
 

    <table class="transections">
        print_r($transactions);
        @foreach ($transactions as $item)
            {{json_encode($item)}}
            <tr>
                <td class="id"></td>
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