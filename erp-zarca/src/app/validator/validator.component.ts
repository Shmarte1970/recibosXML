import { AbstractControl } from '@angular/forms';

export class ExistValidator {
  static ExistValidator(value: string, check: string[]) {
    return (control: AbstractControl) => {
      if (control.value === value) return null
      if (check.includes(control.value)) return { Exist: true }
      return null
    }
  }
}