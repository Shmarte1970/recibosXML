import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-reloj',  
  standalone: true,
  imports: [],
  templateUrl: './reloj.component.html',
  styleUrls: ['./reloj.component.css', '../../../styles.css']

})
export class RelojComponent implements OnInit {
  horaPantalla : string = '';
  

  constructor() { }


  ngOnInit(): void {
    this.mueveReloj();

  }

  mueveReloj(): void {

    const momentoActual = new Date();
    let hora = momentoActual.getHours();
    let minutos = momentoActual.getMinutes();
    
    const dia = momentoActual.getDate().toString().padStart(2,'0');
    const mes = (momentoActual.getMonth() + 1).toString().padStart(2,'0');
    const year = momentoActual.getFullYear().toString();
 
    const str_minuto = minutos.toString().padStart(2, '0');
    
    let ampm = hora >=12 ? 'pm' : 'am';
    hora = hora % 12;
    hora = hora? hora : 12;
    
    const str_hora = hora.toString().padStart(2, '0');

    this.horaPantalla = `${dia}/${mes}/${year} - - ${str_hora}:${str_minuto} (${ampm})`;

    setTimeout(() => this.mueveReloj(), 1000);
    
  }

}
