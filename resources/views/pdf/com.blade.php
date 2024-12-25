<div>
    <p>{{$customer['name']}}</p>
    <p>{{$customer['address']}}</p>
    <p>{{$customer['address_line_1']}}</p>
    <p>{{$customer['address_line_2']}}</p>
    <p>{{$customer['city']}}</p>
    <p>{{$customer['account_number']}}</p>

    <table>
        @foreach ($transactions as $item)
            {{json_encode($item)}}
            <tr>
                <td>{{$item['date']}}</td>
                <td>{{$item['depositType']}}</td>
                <td>{{$item['depositAmount']}}</td>
                <td>{{$item['withdrawalAmount']}}</td>
                <td>{{$item['balance']}}</td>
            </tr>
        @endforeach
    </table>
    <html-separator/>

    <html-separator/>
</div>