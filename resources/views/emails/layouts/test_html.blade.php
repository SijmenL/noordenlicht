@component('mail::message')
    <h1>Recent aangemaakt account:</h1>

    <p>Recent account data:</p>

    Name: {{ $account->name }}
    Email: {{ $account->email }}
    Sex: {{ $account->sex }}
    Infix: {{ $account->infix }}
    Last Name: {{ $account->last_name }}
    Birth Date: {{ $account->birth_date }}
    Street: {{ $account->street }}
    Postal Code: {{ $account->postal_code }}
    City: {{ $account->city }}
    Phone: {{ $account->phone }}
    Avg: {{ $account->avg ? 'Yes' : 'No' }}
    Member Date: {{ $account->member_date }}
    Dolfijnen Name: {{ $account->dolfijnen_name }}
    Children: {{ $account->children }}
    Parents: {{ $account->parents }}


    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
