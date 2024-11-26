import { RouterLink } from "@angular/router";

export const navbarData = [

  {
    routeLink: 'usuarios',
    icon: 'fa fa-solid fa-user',
    label: 'Usuarios',
    roleAccess: "view_usuarios",
  },
  {
    routeLink: 'roles',
    icon: 'fa fa-solid fa-genderless',
    label: 'Roles',
    
    roleAccess: "view_rol",
  },
  {
  routeLink: 'empresas',
  icon: 'fa fa-regular fa-circle-user',
    label: 'Empresas',
    roleAccess: ["view_empresa", "view_all_empresa"],

  },
  {
    routeLink: 'productos',
    icon: 'fa fa-brands fa-product-hunt',
    label: 'Productos',
    roleAccess: "view_producto",
  },
  {
    routeLink: 'ofertas',
    icon: 'fa fa-solid fa-bookmark',
    label: 'Ofertas',
    roleAccess: "view_oferta",
  },
  {
    routeLink: 'contratos',
    icon: 'fa fa-solid fa-file-signature',
    label: 'Contratos',
    roleAccess: "view_contrato",
  },
  {
    routeLink: 'almacenes',
    icon: 'fa fa-solid fa-warehouse',
    label: 'Almacenes',
    roleAccess: "view_almacen",
  },
  {
    routeLink: 'facturacion',
    icon: 'fa fa-solid fa-receipt',
    label: 'Facturacion',
    roleAccess: "view_facturacion",
  },
  {
    routeLink: 'recibos',
    icon: 'fa fa-solid fa-ticket-simple',
    label: 'Recibos',
    roleAccess: "view_recibo",
  },
  {
    routeLink: 'welcome',
    icon: 'fa fa-solid fa-desktop',
    label: 'Dashboard',
    roleAccess: "welcome",
  }
];
