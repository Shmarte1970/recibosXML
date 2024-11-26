import {
  Component,
  EventEmitter,
  OnInit,
  Output,
  AfterViewInit,
  Input,
} from '@angular/core';
import {
  FormControl,
  FormGroup,
  ReactiveFormsModule,
  Validators,
} from '@angular/forms';
import { UserService } from '../../../services/users.service';
import { ToastrService } from 'ngx-toastr';
import { CustomValidators } from '../../../utils/custom-validator';
declare var $: any;

@Component({
  selector: 'app-edit-user-modal',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './edit-user-modal.component.html',
  styleUrl: './edit-user-modal.component.css',
})
export class EditUserModalComponent implements OnInit, AfterViewInit {
  @Output()
  closeModal: EventEmitter<any>;

  @Input()
  selectedUser: any = {};

  userForm: FormGroup;
  name: FormControl = new FormControl('', Validators.required);
  surnames: FormControl = new FormControl('', Validators.required);
  email: FormControl = new FormControl('', [
    Validators.required,
    CustomValidators.validEmail,
  ]);
  phone: FormControl = new FormControl('', Validators.required);
  username: FormControl = new FormControl('', Validators.required);
  password: FormControl = new FormControl('', [CustomValidators.editPassword]);
  specialityId: FormControl = new FormControl('');
  categoryId: FormControl = new FormControl('');
  countryId: FormControl = new FormControl('');
  provinceId: FormControl = new FormControl('');
  cityId: FormControl = new FormControl('');
  postcodeId: FormControl = new FormControl('');
  userRoles: FormControl = new FormControl('');

  specialities: any = [];
  categories: any = [];
  countries: any = [];
  provinces: any = [];
  cities: any = [];
  postcodes: any = [];

  roles: any = [];

  countryForm: FormGroup;
  newCountryName: FormControl = new FormControl('', Validators.required);
  showCountryForm: boolean = false;

  provinceForm: FormGroup;
  newProvinceName: FormControl = new FormControl('', Validators.required);
  showProvinceForm: boolean = false;

  cityForm: FormGroup;
  newCityName: FormControl = new FormControl('', Validators.required);
  showCityForm: boolean = false;

  postcodeForm: FormGroup;
  newPostcode: FormControl = new FormControl('', Validators.required);
  showPostcodeForm: boolean = false;

  showUserForm: boolean = true;

  constructor(public userService: UserService, private toastr: ToastrService) {
    this.closeModal = new EventEmitter<any>();

    this.userForm = new FormGroup({
      name: this.name,
      surnames: this.surnames,
      email: this.email,
      phone: this.phone,
      username: this.username,
      password: this.password,
      specialityId: this.specialityId,
      categoryId: this.categoryId,
      countryId: this.countryId,
      provinceId: this.provinceId,
      cityId: this.cityId,
      postcodeId: this.postcodeId,
      userRoles: this.userRoles,
    });

    this.countryForm = new FormGroup({
      name: this.newCountryName,
    });

    this.provinceForm = new FormGroup({
      name: this.newProvinceName,
      countryId: this.countryId,
    });

    this.cityForm = new FormGroup({
      name: this.newCityName,
      provinceId: this.provinceId,
    });

    this.postcodeForm = new FormGroup({
      postcode: this.newPostcode,
    });
  }

  ngOnInit(): void {
    this.getSpecialities();
    this.getCategories();
    this.getCountries();
    this.getProvinces();
    this.getCities();
    this.getPostcodes();
    this.getRoles();

    // Get roles from user in array format
    const modelUserRoles: any = [];
    for (let i: number = 0; i < this.selectedUser.roles.length; i++) {
      modelUserRoles.push(this.selectedUser.roles[i].id);
    }

    this.userForm.setValue({
      name: this.selectedUser.name,
      surnames: this.selectedUser.surnames,
      email: this.selectedUser.email,
      phone: this.selectedUser.phone,
      username: this.selectedUser.username,
      password: '',
      specialityId: this.selectedUser.speciality?.id || '',
      categoryId: this.selectedUser.category?.id || '',
      countryId: this.selectedUser.country?.id || '',
      provinceId: this.selectedUser.province?.id || '',
      cityId: this.selectedUser.city?.id || '',
      postcodeId: this.selectedUser.postcode?.id || '',
      userRoles: modelUserRoles,
    });
  }

  ngAfterViewInit() {
    $('#roles').select2({
      theme: 'bootstrap-5',
      width: $(this).data('width')
        ? $(this).data('width')
        : $(this).hasClass('w-100')
          ? '100%'
          : 'style',
      placeholder: $(this).data('placeholder'),
      closeOnSelect: false,
    });
  }

  close(state: any) {
    this.closeModal.emit(state);
  }

  getSpecialities() {
    this.userService.getSpecialities().subscribe({
      next: (data) => {
        this.specialities = data.entities;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se han podido obtener las especialidades');
      },
    });
  }

  getCategories() {
    this.categoryId.enable();
    this.userService.getCategories(this.specialityId.value).subscribe({
      next: (data) => {
        this.categories = data.entities;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se han podido obtener las categorías');
      },
    });
  }

  getCountries() {
    this.userService.getCountries().subscribe({
      next: (data) => {
        this.countries = data.entities;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se han podido obtener los países');
      },
    });
  }

  postCountry() {
    this.userService.postCountry(this.countryForm.value).subscribe({
      next: (newCountry) => {
        this.toastr.success('País creado con éxito');
        this.getCountries();
        this.countryId.setValue(newCountry.id);
        this.provinceId.enable();
        this.showCountryForm = false;
        this.showUserForm = true;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se ha podido crear el país');
      },
    });
  }

  openCountryForm() {
    this.showCountryForm = true;
    this.showUserForm = false;
  }

  getProvinces() {
    this.provinceId.enable();
    this.userService.getProvinces(this.countryId.value).subscribe({
      next: (data) => {
        this.provinces = data.entities;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se han podido obtener las provincias');
      },
    });
  }

  postProvince() {
    this.userService.postProvince(this.provinceForm.value).subscribe({
      next: (newProvince) => {
        this.toastr.success('Ciudad creada con éxito');
        this.getProvinces();
        this.provinceId.setValue(newProvince.id);
        this.cityId.enable();
        this.showProvinceForm = false;
        this.showUserForm = true;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se ha podido crear la ciudad');
      },
    });
  }

  openProvinceForm() {
    this.showProvinceForm = true;
    this.showUserForm = false;
  }

  getCities() {
    this.cityId.enable();
    this.userService.getCities(this.provinceId.value).subscribe({
      next: (data) => {
        this.cities = data.entities;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se han podido obtener las ciudades');
      },
    });
  }

  postCity() {
    this.userService.postCity(this.cityForm.value).subscribe({
      next: (newCity) => {
        this.toastr.success('Ciudad creada con éxito');
        this.getCities();
        this.cityId.setValue(newCity.id);
        this.postcodeId.enable();
        this.showCityForm = false;
        this.showUserForm = true;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se ha podido crear la ciudad');
      },
    });
  }

  openCityForm() {
    this.showCityForm = true;
    this.showUserForm = false;
  }

  getPostcodes() {
    this.postcodeId.enable();
    this.userService.getPostcodes().subscribe({
      next: (data) => {
        this.postcodes = data.entities;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se han podido obtener los códigos postales');
      },
    });
  }

  postPostcode() {
    this.userService.postPostcode(this.postcodeForm.value).subscribe({
      next: (newPostcode) => {
        this.toastr.success('Código Postal creado con éxito');
        this.getPostcodes();
        this.postcodeId.setValue(newPostcode.id);
        this.showPostcodeForm = false;
        this.showUserForm = true;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('No se ha podido crear el código postal');
      },
    });
  }

  openPostcodeForm() {
    this.showPostcodeForm = true;
    this.showUserForm = false;
  }

  enablePostcode() {
    this.postcodeId.enable();
  }

  getRoles() {
    this.userService.getRoles().subscribe({
      next: (data) => {
        this.roles = data.entities;
      },
      error: (e) => {
        console.log(e);
        this.toastr.warning('Error al solicitar los datos de los roles');
      },
    });
  }

  openUserForm() {
    this.showUserForm = true;
    this.showCountryForm = false;
    this.showProvinceForm = false;
    this.showCityForm = false;
  }

  editUser() {
    this.userService
      .putUser(this.userForm.value, this.selectedUser.id)
      .subscribe({
        next: (editedUser) => {
          this.toastr.success('Usuario editado correctamente');
          this.setRoles(editedUser.id);
        },
        error: (e) => {
          console.log(e);
          this.toastr.error(`Error al editar el usuario:\n${e.error.errors}`);
        },
      });
  }

  setRoles(userId: any) {
    const selectedRoles = $('#roles').select2('val');

    const body = selectedRoles.map((rol: string) => {
      const i = rol.indexOf(':') + 2;
      return { id: parseInt(rol.slice(i)) };
    });

    this.userService.setRoles(body, userId).subscribe({
      next: (editedUser) => {
        this.close(editedUser);
      },
      error: (e) => {
        this.toastr.error(`No ha sido posible editar los roles del usuario`);
      },
    });
  }
}
