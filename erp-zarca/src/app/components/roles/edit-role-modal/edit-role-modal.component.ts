import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import {
  FormBuilder,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { UserService } from '../../../services/users.service';
import { ToastrService } from 'ngx-toastr';
import { AngularMaterialModule } from '../../../module/app.angular.material.component';
import { CommonModule, Location } from '@angular/common';
import { ModuloService } from '../../../services/modulo.service';

@Component({
  selector: 'app-edit-role-modal',
  standalone: true,
  imports: [ReactiveFormsModule, AngularMaterialModule, CommonModule],
  templateUrl: './edit-role-modal.component.html',
  styleUrl: './edit-role-modal.component.css',
})
export class EditRoleModalComponent implements OnInit {
  columnsInfo: string[] = ["idpermise", "permise", "formula", "observaciones"]
  columnsName: string[] = ["id", "Permiso", "Formula", "Observacion"]
  @Output() closeModal: EventEmitter<any>;
  @Input() selectedRole: any = {};
  roleForm: FormGroup;


  constructor(public userService: UserService, private toastr: ToastrService, public module: ModuloService, private formBuilder: FormBuilder
  ) {
    this.closeModal = new EventEmitter<any>();
    this.roleForm = this.formBuilder.group({
      "id": this.formBuilder.control(""),
      "name": this.formBuilder.control("", Validators.required),
      "permise": this.formBuilder.control([]),
    })
  }

  ngOnInit(): void {
    this.roleForm.patchValue(this.selectedRole);
    this.roleForm.patchValue({
      permise: this.module.cacheTable["zcrolespermise"].data.rows.filter((a: any) => a.idroles === this.selectedRole.id).map((a: any) => a.idpermise)
    });
  }

  close(state: any) {
    this.closeModal.emit(state);
  }

  editRole() {
    this.userService
      .putRole(this.roleForm.value, this.selectedRole.id)
      .subscribe({
        next: async (modifiedRole) => {
          const f = this.module.cacheTable["zcrolespermise"].data.rows.filter((a: any) => a.idroles === this.selectedRole.id)
          for (const x of f) {
            if (x) {
              await this.module.ModuloTableDeleteElement("zcrolespermise", { idrolespermise: x.idrolespermise })
            }
          }
          for (const r of this.roleForm.getRawValue().permise) {
            await this.module.ModuloTableCreateElement("zcrolespermise", { idroles: this.selectedRole.id, idpermise: r })
          }
          await this.module.UpdateModuleTable("zcrolespermise")
          window.location.reload();
          this.toastr.success('Rol modificado correctamente');
          this.close(modifiedRole);
        },
        error: (e) => {
          console.log(e);
          this.toastr.error('Error al modificar el rol');
        },
      });
  }
  checkBoxPermise(event: any, item: any) {
    if (event.target.checked) {
      const x = [... this.roleForm.controls['permise'].value, item.idpermise]
      this.roleForm.patchValue({ 'permise': x })
    } else {
      const x = []
      for (const y of this.roleForm.controls['permise'].value) {
        if (y !== item.idpermise) x.push(y)
      }
      this.roleForm.patchValue({ 'permise': x })
    }
    console.log(this.roleForm.controls['permise'].value)
  }
}
