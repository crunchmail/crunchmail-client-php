

    list = cli->contacts->lists->get(id)

    list->merge($objects)
    list->merge($ids)
    list->clone()

    list->addProperty(name, values)
    list->editProperty(name, values)
    list->deleteProperty(name)

    list->contact->post(values)

    contact = list->contacts->get(id)

    contact->clone(list_id)


    queue->consume()
        ==> queue->post();

    queue->get()
    queue->debug() ?

    list->importCSV(content)
    list->exportCSV()
