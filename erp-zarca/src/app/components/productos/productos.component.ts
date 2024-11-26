import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, FormsModule, Validator, Validators } from '@angular/forms';
import { ProductosService } from '../../services/productos.service';
import { ComponenteLoadingComponent } from '../componente/loading/loading.component';
import { UserService } from '../../services/users.service';
import { AngularMaterialModule } from '../../module/app.angular.material.component';
import { componenteFormularioComponent } from './modal/formulario.component';
import { ToastrService } from 'ngx-toastr';
import { zcAccion } from './interface';
import { PermiseService } from '../../services/permise.service';
import { RelojComponent } from '../reloj/reloj.component';

@Component({
  selector: 'app-productos',
  standalone: true,
  imports: [
    CommonModule,
    ComponenteLoadingComponent,
    AngularMaterialModule,
    FormsModule,
    componenteFormularioComponent,
    RelojComponent,
  ],
  templateUrl: './productos.component.html',
  styleUrls: ['./productos.component.css', '../../../styles.css']
})

export class ProductosComponent implements OnInit {
  userName: string = JSON.parse(sessionStorage.getItem('userData') || "")?.user?.username;

  constructor(
    public module: ProductosService,
    private formBuilder: FormBuilder,
    private toastr: ToastrService,
    public permise: PermiseService
  ) { }

  columns: { key: string, Name: string }[] =
    [{ key: "idProductos", Name: "id", },
    { key: "propietario", Name: "Propietario", },
    { key: "posicionflota", Name: "flota", },
    { key: "estadoProducto", Name: "estadoProducto", }]

  ProductoForm!: FormGroup
  isAdmin: boolean = false
  Accion: zcAccion = "Crear"
  Name: String = "Producto"
  cacheList: string[] = [
    "zcproductos", "zcpropietario", "zcflota", "zcubicacionalmacen", "zcestadoProducto", "zcelementosProducto",
    "zcrelacion"
  ]

  dialog: boolean = false
  ngOnInit(): void {
    this.module.loading = true
    this.getData();
    this.ProductoForm = this.formBuilder.group({
      "idProductos": this.formBuilder.control(""),
      "nombreProductoTipo": this.formBuilder.control("", Validators.required),
      "propietario": this.formBuilder.control("", Validators.required),
      "enStock": this.formBuilder.control("", Validators.required),
      "posicionflota": this.formBuilder.control("", Validators.required),
      "ubicacionalmacen": this.formBuilder.control("", Validators.required),
      "Matricula": this.formBuilder.control("", Validators.required),
      "Longitud": this.formBuilder.control("", Validators.required),
      "Altura": this.formBuilder.control("", Validators.required),
      "estadoproducto": this.formBuilder.control("", Validators.required),
      "precioCompra": this.formBuilder.control(""),
    },);
  }

  async getData() {
    setTimeout(async () => {
      const total = this.cacheList.length - 1
      let progress = 0
      await new Promise<void>((resolve) => {
        for (const table of this.cacheList) this.module.getProductosTable(table).finally(() => progress === total ? resolve() : progress++)
      })
      this.pDatas = JSON.parse(JSON.stringify(this.module.cacheTable[this.cacheList[0]]))
      this.module.loading = false
    }, 0);


  }

  Do() {
    this.module.loading = true
    setTimeout(async () => {
      try {
        if (this.Accion === "Activar") {

          this.sData.enable = !this.sData.enable
          this.ProductoForm.patchValue(this.sData)
          await this.module.ProductosTableUpdateElement(this.cacheList[0], this.ProductoForm.getRawValue())
          await this.module.UpdateProductosTable(this.cacheList[0])
        } else if (this.Accion === "Crear") {

          this.ProductoForm.patchValue({ enable: 1 })
          if (this.ProductoForm.status === "VALID") {
            const data = await this.module.ProductosTableCreateElement(this.cacheList[0], this.ProductoForm.getRawValue())
            this.ProductoForm.patchValue({ idProductos: data.idProductos })
            const data2 = await this.module.ProductosTableUpdateElement(this.cacheList[0], this.ProductoForm.getRawValue())
            await this.module.UpdateProductosTable(this.cacheList[0])
            this.toastr.success('Acción realizada con Exito!!');
            this.sData = data2
            this.ProductoForm.patchValue(data2)
            this.openModal = false
            this.dialog = true
          } else this.ProductoForm.markAllAsTouched()
        } else if (this.Accion === "Editar") {
          if (this.ProductoForm.status === "VALID") {
            await this.module.ProductosTableUpdateElement(this.cacheList[0], this.ProductoForm.getRawValue())
            await this.module.UpdateProductosTable(this.cacheList[0])
            this.toastr.success('Acción realizada con Exito!!');
            this.openModal = false
          } else this.ProductoForm.markAllAsTouched()
        }
      } catch (e) { }
      this.pDatas = JSON.parse(JSON.stringify(this.module.cacheTable[this.cacheList[0]]))
      this.Search()
      this.module.loading = false
    }, 0);
  }

  Buscar!: string
  Paginador: [number, number] = [0, 15]
  sData!: any
  pDatas!: any
  openModal: boolean = false

  Search() {
    const data = this.module.cacheTable[this.cacheList[0]];
    this.pDatas.data.rows = this.Buscar ?
      data.data.rows.filter((a: any) =>
        (a.nombreProductoTipo).toUpperCase().startsWith(this.Buscar.toUpperCase()) ||
        (a.ubicacionProducto && a.ubicacionProducto.toUpperCase().startsWith(this.Buscar.toUpperCase()))
      ) : data.data.rows;
    this.Paginador[1] = 10;
  }


  onscroll(event: any): void {
    if ((event.target.offsetHeight + event.target.scrollTop) >= event.target.scrollHeight)
      this.Paginador[1] += this.Paginador[1] + 10 > this.pDatas.data.count ? this.pDatas.data.count : this.Paginador[1] + 10
  }


}
