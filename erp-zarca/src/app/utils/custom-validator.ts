import { AbstractControl, ValidationErrors } from '@angular/forms';

export class CustomValidators {
  static validPassword(control: AbstractControl): ValidationErrors | null {
    const nameRegexp: RegExp =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/;
    if (!nameRegexp.test(control.value)) {
      return { validPassword: true };
    }
    return null;
  }

  static editPassword(control: AbstractControl): ValidationErrors | null {
    const nameRegexp: RegExp =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/;
    if (control.value && !nameRegexp.test(control.value)) {
      return { editPassword: true };
    }
    return null;
  }

  static validEmail(control: AbstractControl): ValidationErrors | null {
    const nameRegexp: RegExp =
      /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!nameRegexp.test(control.value)) {
      return { validEmail: true };
    }
    return null;
  }
}
