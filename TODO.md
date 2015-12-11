
# Rethink


- Exception system : RuntimeException vs Exception\ClientException

# Missing

- Domain validate ?
- Domain revalidate

    $domain->revalidate()

- Stats
- Category stats
- Archive => send content ?
- Spam details
- preview links test

- More filter() tests
- More request sent tests

- Put on current object with current values:

    $message->title = 'Hello';
    $edit = $message->put();
    $edit = $message->put($values);

# Missing tests

- opt-outs

    $client->optouts->get()
    $message->ouptout->get()

- bounces

    $client->bounces->get()
    $message->bounces->get()

- verify delete tests

- unset(\$message->title) throws an error
- body protected ?
- raw requests


# fix

- preview conflict -> rename to ? direct method ?
