import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class UserService {
  //private readonly API_URL = 'http://localhost:8080/api/v1';    // habilitar esta linea para Jarko y para mi en local 
  private readonly API_URL = 'http://192.168.0.16:8080/api/v1';  // habilitar esta linea para el trabajo en el servidor del erp
  private token = '';
  private options = {};
  public currentUser: any | null = null;

  constructor(private http: HttpClient) {}

  /********************************** LOGIN **********************************/

  login(body: any) {
    return this.http.post<any>(`${this.API_URL}/auth/login`, body);
  }

  logout() {
    this.token = '';
    this.options = {};
    this.currentUser = null;
  }

  setUser(data: any) {
    this.token = data.token;

    this.options = {
      headers: new HttpHeaders({
        Authorization: `Bearer ${this.token}`,
      }),
    };

    this.currentUser = data.user;
  }

  isLoggedIn(): boolean {
    return !!this.currentUser;
  }

  isAdmin() {
    return this?.currentUser.roles.some((rol: any) => rol.id === 1);
  }

  /********************************** USERS **********************************/

  getUsers() {
    return this.http.get<any>(`${this.API_URL}/users`, this.options);
  }

  postUser(body: any) {
    return this.http.post<any>(`${this.API_URL}/users`, body, this.options);
  }

  putUser(body: any, userId: number) {
    return this.http.put<any>(
      `${this.API_URL}/users/${userId}`,
      body,
      this.options
    );
  }

  deleteUser(userId: number) {
    return this.http.delete<any>(
      `${this.API_URL}/users/${userId}`,
      this.options
    );
  }

  addRoleToUser(userId: number, roleId: string) {
    return this.http.patch<any>(
      `${this.API_URL}/users/${userId}/addRole/${roleId}`,
      null,
      this.options
    );
  }

  setRoles(body: any, userId: number) {
    return this.http.patch<any>(
      `${this.API_URL}/users/${userId}/setRoles`,
      body,
      this.options
    );
  }

  enableUser(userId: number) {
    return this.http.patch<any>(
      `${this.API_URL}/users/${userId}/enable`,
      null,
      this.options
    );
  }

  disableUser(userId: number) {
    return this.http.patch<any>(
      `${this.API_URL}/users/${userId}/disable`,
      null,
      this.options
    );
  }

  searchUsers(typeOfSearch: string, searched: string) {
    console.log(`${this.API_URL}/users` + '?' + typeOfSearch + '=' + searched);
    return this.http.get<any>(
      `${this.API_URL}/users` + '?' + typeOfSearch + '=' + searched,
      this.options
    );
  }

  /******************************* SPECIALITIES ******************************/

  getSpecialities() {
    return this.http.get<any>(`${this.API_URL}/specialities`, this.options);
  }

  /******************************** CATEGORIES *******************************/

  getCategories(specialityId: number) {
    return this.http.get<any>(
      `${this.API_URL}/categories?specialityId=${specialityId}`,
      this.options
    );
  }

  /******************************** COUNTRIES ********************************/

  getCountries() {
    return this.http.get<any>(`${this.API_URL}/countries`, this.options);
  }

  postCountry(body: any) {
    return this.http.post<any>(`${this.API_URL}/countries`, body, this.options);
  }

  /******************************** PROVINCES ********************************/

  getProvinces(countryId: number) {
    return this.http.get<any>(
      `${this.API_URL}/provinces?countryId=${countryId}`,
      this.options
    );
  }

  postProvince(body: any) {
    return this.http.post<any>(`${this.API_URL}/provinces`, body, this.options);
  }

  /********************************** CITIES *********************************/

  getCities(provinceId: number) {
    return this.http.get<any>(
      `${this.API_URL}/cities?provinceId=${provinceId}`,
      this.options
    );
  }

  postCity(body: any) {
    return this.http.post<any>(`${this.API_URL}/cities`, body, this.options);
  }

  /********************************** ROLES **********************************/

  getRoles() {
    return this.http.get<any>(`${this.API_URL}/roles`, this.options);
  }

  postRole(body: any) {
    return this.http.post<any>(`${this.API_URL}/roles`, body, this.options);
  }

  putRole(body: any, roleId: number) {
    return this.http.put<any>(
      `${this.API_URL}/roles/${roleId}`,
      body,
      this.options
    );
  }

  deleteRole(roleId: number) {
    return this.http.delete<any>(
      `${this.API_URL}/roles/${roleId}`,
      this.options
    );
  }
}
