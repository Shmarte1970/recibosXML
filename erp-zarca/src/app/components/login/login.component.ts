import { Component } from '@angular/core';
import { environment } from '../../../environments/environment';
import { UserService } from '../../services/users.service';
import { ToastrService } from 'ngx-toastr';
import {
  FormControl,
  ReactiveFormsModule,
  FormGroup,
  Validators,
} from '@angular/forms';
import { Router } from '@angular/router';
import { ModuloService } from '../../services/modulo.service';
import { RelojComponent } from '../reloj/reloj.component';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, RelojComponent],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  loginForm: FormGroup;
  username: FormControl;
  password: FormControl;
  showPass: string = 'password';

  constructor(
    public userService: UserService,
    private toastr: ToastrService,
    private router: Router,
    private module: ModuloService
  ) {
    /*
    this.username = new FormControl((!environment.production)?'':'', Validators.required);
    this.password = new FormControl((!environment.production)?'':'', Validators.required);
*/
    this.username = new FormControl;
    this.password = new FormControl;

    this.loginForm = new FormGroup({
      username: this.username,
      password: this.password,
    });
  }

  login() {
    this.userService.login(this.loginForm.value).subscribe({
      next: async (data) => {
        this.userService.setUser(data);
        sessionStorage.setItem('userData', JSON.stringify(data));
        await this.module.UpdateModuleTable("zcpermise")
        await this.module.UpdateModuleTable("zcrolespermise")
        this.toastr.success('Has iniciado sesión correctamente');
        this.router.navigate(['/welcome']);
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('Error al iniciar sesión');
      },
    });
  }

  togglePassword() {
    if (this.showPass === 'password') {
      this.showPass = 'text';
    } else if (this.showPass === 'text') {
      this.showPass = 'password';
    }
  }


}
