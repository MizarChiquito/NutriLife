// ----- Catálogo de alimentos -----
const catalogoAlimentos = [
    {id:1, nombre:"Manzana", calorias:52},
    {id:2, nombre:"Pan Integral", calorias:250},
    {id:3, nombre:"Pollo a la plancha", calorias:165},
    {id:4, nombre:"Arroz", calorias:130},
    {id:5, nombre:"Leche", calorias:42}
];

// ----- Abrir/Cerrar modales -----
function abrirModalAlimentos() { document.getElementById('modal_alimentos').style.display='block'; }
function cerrarModalAlimentos() { document.getElementById('modal_alimentos').style.display='none'; }
function abrirModalPaciente() { document.getElementById('modal_paciente').style.display='block'; }
function cerrarModalPaciente() { document.getElementById('modal_paciente').style.display='none'; }

// ----- Eventos botones modales -----
document.getElementById('abrir_alimentos').addEventListener('click', abrirModalAlimentos);
document.getElementById('asignar_plan').addEventListener('click', abrirModalPaciente);

// ----- Agregar alimento al calendario -----
function agregarAlimento(id){
    const input = document.querySelector(`.porcion-input[data-id="${id}"]`);
    const porcion = input.value;
    if(!porcion || porcion<=0){ alert("Ingrese una porción válida"); return; }

    const alimento = catalogoAlimentos.find(a=>a.id===id);
    const dia = prompt("Ingrese el día (Lunes-Domingo):");
    const comida = prompt("Ingrese la comida (Desayuno, Comida, Cena, Snack):");

    const diaDiv = document.querySelector(`.dia[data-dia="${dia}"]`);
    if(!diaDiv){ alert("Día inválido"); return; }

    const alimentoDiv = document.createElement('div');
    alimentoDiv.className='alimento-card';
    alimentoDiv.innerHTML=`${alimento.nombre} - ${porcion}g [${comida}]`;
    diaDiv.appendChild(alimentoDiv);

    input.value='';
}

// ----- Buscar alimentos -----
document.getElementById('buscar_alimento').addEventListener('input', function(){
    const filtro = this.value.toLowerCase();
    const resultados = catalogoAlimentos.filter(a=>a.nombre.toLowerCase().includes(filtro));
    mostrarResultadosAlimentos(resultados);
});

function mostrarResultadosAlimentos(lista){
    const cont = document.getElementById('resultados_alimentos');
    cont.innerHTML='';
    lista.forEach(alimento=>{
        const div = document.createElement('div');
        div.className='alimento-card';
        div.innerHTML=`
            ${alimento.nombre} (${alimento.calorias} kcal)
            <input type="number" class="porcion-input" placeholder="g" min="1" step="1" data-id="${alimento.id}">
            <button onclick="agregarAlimento(${alimento.id})">Agregar</button>
        `;
        cont.appendChild(div);
    });
}

// ----- Guardar plan -----
document.getElementById('guardar_plan').addEventListener('click', async ()=>{
    const nombre = document.getElementById('nombre_plan').value;
    const descripcion = document.getElementById('descripcion_plan').value;
    if(!nombre){ alert("Ingrese un nombre de plan"); return; }

    const detalle = [];
    document.querySelectorAll('.dia').forEach(diaDiv=>{
        const dia = diaDiv.dataset.dia;
        diaDiv.querySelectorAll('.alimento-card').forEach(alimentoDiv=>{
            const texto = alimentoDiv.innerText;
            const [nombreAlimento, resto] = texto.split(' - ');
            const porcion = parseFloat(resto.split('g')[0]);
            const comidaMatch = resto.match(/\[(.*?)\]/);
            const comida = comidaMatch ? comidaMatch[1] : 'Comida';
            const alimentoObj = catalogoAlimentos.find(a=>a.nombre===nombreAlimento);
            if(alimentoObj){
                detalle.push({dia, comida, alimento_id: alimentoObj.id, porcion});
            }
        });
    });

    try{
        const response = await fetch('http://localhost/NutriLife/NutriLife/PHP/planes_plantillas.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({nombre, descripcion, detalle})
        });
        const result = await response.json();
        if(result.success){
            document.getElementById('mensaje_plan').innerText = "Plan guardado con éxito!";
            document.getElementById('nombre_plan').dataset.planId = result.id;
        } else {
            document.getElementById('mensaje_plan').innerText = "Error al guardar el plan";
        }
    } catch(err){ console.error(err); alert("Error en la conexión al servidor"); }
});

// ----- Cargar plantillas -----
document.getElementById('cargar_plantillas').addEventListener('click', async ()=>{
    try{
        const response = await fetch('http://localhost/NutriLife/NutriLife/PHP/planes_plantillas.php');
        const plantillas = await response.json();
        const cont = document.getElementById('plantillas_listado');
        cont.innerHTML='';

        plantillas.forEach(plan=>{
            const div = document.createElement('div');
            div.className='alimento-card';
            div.innerHTML=`
                <strong>${plan.nombre}</strong> - ${plan.descripcion}
                <button class="usar-plan" data-id="${plan.id}">Usar Plan</button>
            `;
            cont.appendChild(div);
        });

        document.querySelectorAll('.usar-plan').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const planId = btn.dataset.id;
                const plan = plantillas.find(p=>p.id==planId);
                if(!plan){ alert("Plan no encontrado"); return; }

                // Limpiar calendario
                document.querySelectorAll('.dia').forEach(d=>d.innerHTML=`<h4>${d.dataset.dia}</h4>`);

                // Nombre y descripción
                document.getElementById('nombre_plan').value = plan.nombre;
                document.getElementById('descripcion_plan').value = plan.descripcion;
                document.getElementById('nombre_plan').dataset.planId = plan.id;

                // Cargar alimentos
                plan.detalle.forEach(item=>{
                    const diaDiv = document.querySelector(`.dia[data-dia="${item.dia}"]`);
                    if(diaDiv){
                        const alimento = catalogoAlimentos.find(a=>a.id==item.alimento_id);
                        const alimentoDiv = document.createElement('div');
                        alimentoDiv.className='alimento-card';
                        alimentoDiv.innerHTML=`${alimento.nombre} - ${item.porcion}g [${item.comida}]`;
                        diaDiv.appendChild(alimentoDiv);
                    }
                });

                alert(`Plan "${plan.nombre}" cargado.`);
            });
        });

    } catch(err){ console.error(err); alert("Error al cargar plantillas"); }
});

// ----- Asignar plan a paciente -----
document.getElementById('buscar_paciente').addEventListener('input', async function(){
    const filtro = this.value.toLowerCase();
    const cont = document.getElementById('resultados_pacientes');
    cont.innerHTML='';

    try{
        const response = await fetch('http://localhost/NutriLife/NutriLife/PHP/pacientes_nutriologo.php');
        const pacientes = await response.json();

        const resultados = pacientes.filter(p=> (p.first_name + ' ' + p.last_name).toLowerCase().includes(filtro));

        resultados.forEach(p=>{
            const div = document.createElement('div');
            div.className='paciente-card';
            div.innerHTML=`
                ${p.first_name} ${p.last_name} 
                <button onclick="asignarPlanPaciente(${p.paciente_id})">Asignar Plan</button>
            `;
            cont.appendChild(div);
        });

    } catch(err){ console.error(err); alert("Error al cargar pacientes"); }
});

async function asignarPlanPaciente(pacienteId){
    const planId = document.getElementById('nombre_plan').dataset.planId;
    if(!planId){ alert("Primero guarda o selecciona un plan"); return; }

    try{
        const response = await fetch('http://localhost/NutriLife/NutriLife/PHP/asignar_planes.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ paciente_id: pacienteId, plan_id: planId })
        });
        const result = await response.json();
        if(result.success){
            alert("Plan asignado con éxito");
            cerrarModalPaciente();
        } else alert("Error al asignar plan: " + (result.mensaje||''));
    } catch(err){ console.error(err); alert("Error de conexión"); }
}
