export interface zcControl {
    Key: string,
    Required?: boolean,
    Name?: string,
    Long?: string,
    Table?: zcTable,
    Input?: zcInput,
    Textarea?: zcInput
    Select?: zcSelect,
    Checkbox?: zcCheckbox,
    Class?: zcClass

}

interface zcTable {
    Name: string
    Large?: number,
    Table?: string,
    Filtro?: zcFiltro,
    Form?: zcControl[],
    Columns?: zcControl[]
}

interface zcInput {
    Name: string,
    Placeholder?: string
    Large?: number
}
interface zcClass {
    Label?: string,
    Input?: string,
    Field?: string,
    Textarea?: string,
    Table?: string,
    Checkbox?: string,
    Select?: string,
    Option?: string
}
interface zcSelect {
    Name: string,
    Table?: string,
    Dep?: string,
    BeDep?: string,
    Form?: zcControl[],
    Option?: zcOption,
    Multiple?: boolean,
    Filtro?: zcFiltro,
}
interface zcCheckbox {
    Name: string,
}
interface zcFiltro {
    InterTable: string,
    InterFkey: string,
    InterSKey: string,
    FilterFKey: string,
    FilterSKey: string,
    Filtro?: zcFiltro
}

export interface zcPestanya {
    Name: string,
    New: boolean,
    Form: string[]
}

interface zcOption {
    idKey: string, NameKey: string
}
export type zcAccion = "Ver" | "Crear" | "Activar" | "Editar" | "Desasignar" | "Asignar"