import { FormsModule } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { UserService } from '../../services/users.service';
import { ToastrService } from 'ngx-toastr';
import { NgClass } from '@angular/common';
import { toggleModal } from '../../utils/animations';
import { CreateUserModalComponent } from './create-user-modal/create-user-modal.component';
import { DeleteModalComponent } from '../delete-modal/delete-modal.component';
import { EditUserModalComponent } from './edit-user-modal/edit-user-modal.component';
import { RelojComponent } from '../reloj/reloj.component';

@Component({
  selector: 'app-usuarios',
  standalone: true,
  imports: [
    NgClass,
    CreateUserModalComponent,
    EditUserModalComponent,
    DeleteModalComponent,
    FormsModule,
    RelojComponent
  ],
  templateUrl: './usuarios.component.html',
  styleUrls: ['./usuarios.component.css', '../../../styles.css'],
  animations: [toggleModal],
})
export class UsuariosComponent implements OnInit {
  users: any[] = [];
  orderedUsers: any[] = [];
  currentOrder: string = '';
  descendant: boolean = false;
  selectedUser: any = {};
  selectedUserId: number = 0;
  showCreateModal: boolean = false;
  showEditModal: boolean = false;
  showDeleteModal: boolean = false;
  searchText: string = '';
  searchName: string = '';
  searchUsername: string = '';
  showPass: string = 'password';
  searchObj: { [key: string]: string } = {};
  userName: string = JSON.parse(sessionStorage.getItem('userData') || "")?.user?.username;;

  constructor(public userService: UserService, private toastr: ToastrService) { }

  ngOnInit(): void {
    this.getUsers();
  }

  getUsers() {
    this.userService.getUsers().subscribe({
      next: (data) => {
        this.users = data.entities;
        this.orderBy('id');
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('Error al solicitar los datos de los usuarios');
      },
    });
  }

  searchUsers(type: string[], searchedText: string[]) {
    if (type[0] === 'text') {
      this.searchObj = {};
      this.searchText = '';
      this.searchUsername = '';
    }
    type.forEach((element, index) => {
      this.searchObj[element] = searchedText[index];
    });
    this.userService
      .searchUsers(this.searchObj, this.currentOrder, this.descendant)
      .subscribe({
        next: (data) => {
          this.users = data.entities;
          this.orderBy(this.currentOrder, true);
        },
        error: (e) => {
          console.log(e);
          this.toastr.warning('Error al solicitar los datos de los usuarios');
        },
      });
  }

  orderBy(property: string, keepOrder?: boolean, avoid?: boolean) {
    if (property === this.currentOrder && !keepOrder)
      this.descendant = !this.descendant;
    else if (property !== this.currentOrder && !keepOrder) this.descendant = false;
    this.currentOrder = property;

    this.orderedUsers = this.users.toSorted((a, b) => {
      if (this.descendant) return b[property] < a[property] ? -1 : 1;
      return a[property] < b[property] ? -1 : 1;
    });
  }

  selectUser(user: any) {
    this.selectedUser = user;
    this.selectedUserId = user.id;
  }

  isAdmin() {
    return this.userService.isAdmin();
  }

  addUserToList(user: any) {
    this.users.unshift(user);
    this.orderedUsers.unshift(user);
    this.selectedUser = user;
    this.selectedUserId = user.id;
  }

  replaceUser(modifiedUser: any) {
    const usersIndex = this.users.indexOf(this.selectedUser);
    const orderedIndex = this.orderedUsers.indexOf(this.selectedUser);
    this.users.splice(usersIndex, 1, modifiedUser);
    this.orderedUsers.splice(orderedIndex, 1, modifiedUser);
    this.selectedUser = modifiedUser;
    this.selectedUserId = modifiedUser.id;
  }

  openCreateModal() {
    this.showCreateModal = true;
  }

  openEditModal() {
    this.showEditModal = true;
  }

  openDeleteModal() {
    this.showDeleteModal = true;
  }

  closeCreateModal(e: any) {
    this.showCreateModal = false;
    if (e.id) this.addUserToList(e);
  }

  closeEditModal(e: any) {
    this.showEditModal = false;
    if (e.id) this.replaceUser(e);
  }

  closeDeleteModal() {
    this.showDeleteModal = false;
  }

  toggleEnable() {
    if (!this.selectedUser.enabled)
      this.userService.enableUser(this.selectedUserId).subscribe({
        next: (modifiedUser) => {
          this.toastr.success('Usuario Activado');
          this.replaceUser(modifiedUser);
        },
        error: (e) => {
          console.log(e);
          this.toastr.warning('No ha sido posible activar el usuario');
        },
      });
    else
      this.userService.disableUser(this.selectedUserId).subscribe({
        next: (modifiedUser) => {
          this.toastr.success('Usuario desactivado');
          this.replaceUser(modifiedUser);
        },
        error: (e) => {
          console.log(e);
          this.toastr.warning('No ha sido posible desactivar el usuario');
        },
      });
  }

  deleteUser() {
    this.closeDeleteModal();
    this.userService.deleteUser(this.selectedUserId).subscribe({
      next: (data) => {
        this.toastr.success('Usuario eliminado correctamente');
        this.getUsers();
        this.selectedUser = {};
        this.selectedUserId = 0;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('Error al eliminar el usuario');
      },
    });
  }

  btnEditUsuario() {
    this.toastr.info('Editar Usuario 😎');
  }

  onSearchChange() {
    this.searchUsers(['text'], [this.searchText]);
  }

  searchByName() {
    this.searchUsers(['name'], [this.searchName]);
  }

  searchByUsername() {
    this.searchUsers(['username'], [this.searchUsername]);
  }
}
