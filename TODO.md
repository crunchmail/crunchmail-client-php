
# Rethink

- Exception system : RuntimeException vs Exception\ClientException
- Add underscore to private properties to avoid conflicts

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

- verify delete tests

- unset(\$message->title) throws an error
- test raw requests


# fix

- preview conflict -> rename to ? direct method ?
