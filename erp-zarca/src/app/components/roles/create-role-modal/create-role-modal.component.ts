import { Component, EventEmitter, Output } from '@angular/core';
import {
  FormControl,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { UserService } from '../../../services/users.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-create-role-modal',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './create-role-modal.component.html',
  styleUrl: './create-role-modal.component.css',
})
export class CreateRoleModalComponent {
  @Output()
  closeModal: EventEmitter<any>;

  roleForm: FormGroup;
  name: FormControl = new FormControl('', Validators.required);

  constructor(public userService: UserService, private toastr: ToastrService) {
    this.closeModal = new EventEmitter<any>();

    this.roleForm = new FormGroup({
      name: this.name,
    });
  }

  close(state: any) {
    this.closeModal.emit(state);
  }

  createRole() {
    this.userService.postRole(this.roleForm.value).subscribe({
      next: (newRole) => {
        this.toastr.success('Rol añadido correctamente');
        this.close(newRole);
      },
      error: (e) => {
        console.log(e);
        this.toastr.error('Error al crear el nuevo rol');
      },
    });
  }
}
