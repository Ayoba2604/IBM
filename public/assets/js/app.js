document.querySelectorAll('form').forEach(form => form.addEventListener('submit', event => {
  const submitter = event.submitter;
  if (submitter && submitter.value === 'adjust' && !confirm('Enviar solicitação de ajustes ao aluno?')) event.preventDefault();
}));
