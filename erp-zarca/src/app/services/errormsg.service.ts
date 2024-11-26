import { Injectable } from '@angular/core';

@Injectable({
    providedIn: 'root',
})
export class ErrorMsgService {
    Error(c: any, control: string): string {
        let msg = ""
        if (c[control].hasError("required")) msg += "El campo es requerido."
        if (c[control].hasError("maxLength")) msg += "El campo es más largo de lo permitido."
        if (c[control].hasError("minLength")) msg += "El campo es más corto de lo permitido."
        if (c[control].hasError("pattern")) msg += "El campo tiene un formato no permitido."
        if (c[control].hasError("Exist")) msg += "El campo ya existe."
        return msg
    }
}