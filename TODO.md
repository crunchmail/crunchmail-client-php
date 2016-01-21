
# Chain methods ?

$client->multipart()
       ->contentType('text/csv')
       ->post()


-> return the client with a different format / content type




# TODO

getSubResources() return links mapped
documentation

# Rethink

- Exception system : RuntimeException vs Exception\ClientException

# Missing

- Domain revalidate
    $domain->revalidate()

- Stats
- Category stats
- Archives
- Spam details
- More filter() tests
- More request sent tests

# Missing tests

- opt-outs

    $client->optouts->get()
    $message->ouptout->get()

- bounces

    $client->bounces->get()
    $message->bounces->get()

- unset(\$message->title) throws an error (magic method missing)
- test raw requests
