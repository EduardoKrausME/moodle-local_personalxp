<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brazilian Portuguese strings for Personal XP.
 *
 * @package    local_personalxp
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['activitycompleted'] = 'Atividade concluída';
$string['activitycompletedwithname'] = 'Concluiu: {$a}';
$string['aienabled'] = 'Usar IA para refinar o XP das atividades';
$string['aienabled_desc'] = 'Enfileira uma análise pelo AI Bridge após criar a atividade ou alterar conteúdo relevante para a pontuação. Se a IA estiver indisponível, permanece a estimativa local determinística.';
$string['coursecompleted'] = 'Curso concluído';
$string['currentlevel'] = 'Nível atual';
$string['defaultlevel'] = 'Iniciante';
$string['enabled'] = 'Ativar XP Pessoal';
$string['enabled_desc'] = 'Concede XP de aprendizagem pessoal sem ranking, pódio ou comparação entre alunos.';
$string['forumdailymax'] = 'Limite diário de XP no fórum';
$string['forumdailymax_desc'] = 'Máximo de XP de fórum que o aluno pode receber por curso a cada dia.';
$string['forumparticipation'] = 'Participação no fórum';
$string['highestlevel'] = 'Você alcançou o nível mais alto configurado.';
$string['lastgain'] = 'Último XP';
$string['level'] = 'Nível';
$string['levelapprentice'] = 'Aprendiz';
$string['levelbeginner'] = 'Iniciante';
$string['levelexplorer'] = 'Explorador';
$string['levelmaster'] = 'Mestre';
$string['levelpractitioner'] = 'Praticante';
$string['levels'] = 'Níveis';
$string['levels_desc'] = 'Um nível por linha no formato XP|Nome, por exemplo 700|Praticante. A lista é ordenada automaticamente pelo XP.';
$string['levelspecialist'] = 'Especialista';
$string['maxactivityxp'] = 'XP máximo por atividade';
$string['maxactivityxp_desc'] = 'Limite superior aplicado depois que tempo e dificuldade são convertidos em XP.';
$string['minactivityxp'] = 'XP mínimo por atividade';
$string['minactivityxp_desc'] = 'Limite inferior aplicado depois que tempo e dificuldade são convertidos em XP.';
$string['myxp'] = 'Meu XP Pessoal';
$string['nextlevel'] = 'Faltam {$a->xp} XP para {$a->level}';
$string['nohistory'] = 'Você ainda não conquistou XP neste curso.';
$string['openreport'] = 'Abrir relatório de XP do curso';
$string['participant'] = 'Participante';
$string['personalxp:view'] = 'Ver o próprio XP Pessoal';
$string['personalxp:viewreport'] = 'Ver o relatório de XP Pessoal do curso';
$string['pluginname'] = 'XP Pessoal';
$string['privacy:metadata:log'] = 'Armazena o histórico imutável de XP concedido aos alunos.';
$string['privacy:metadata:log:courseid'] = 'O curso em que o XP foi conquistado.';
$string['privacy:metadata:log:label'] = 'A descrição legível da origem do XP.';
$string['privacy:metadata:log:rulekey'] = 'A regra interna que gerou o XP.';
$string['privacy:metadata:log:timecreated'] = 'Quando o XP foi concedido.';
$string['privacy:metadata:log:userid'] = 'O aluno que recebeu o XP.';
$string['privacy:metadata:log:xp'] = 'A quantidade de XP concedida.';
$string['privacy:metadata:user'] = 'Armazena o total agregado de XP por aluno e curso.';
$string['privacy:metadata:user:courseid'] = 'O curso relacionado ao total.';
$string['privacy:metadata:user:timemodified'] = 'Quando o total foi atualizado pela última vez.';
$string['privacy:metadata:user:totalxp'] = 'O total de XP do aluno no curso.';
$string['privacy:metadata:user:userid'] = 'O aluno cujo total está armazenado.';
$string['quizsubmitted'] = 'Tentativa de questionário enviada';
$string['recenthistory'] = 'Histórico recente de XP';
$string['report'] = 'Relatório de XP Pessoal';
$string['reportnocompetition'] = 'Este relatório propositalmente não ordena os alunos por XP. O XP Pessoal mede o progresso de cada aluno e não é uma competição.';
$string['source'] = 'Origem';
$string['totalxpvalue'] = '{$a} XP conquistados';
$string['when'] = 'Quando';
$string['xp'] = 'XP';
$string['xpactivity'] = 'XP por conclusão de atividade';
$string['xpactivity_desc'] = 'XP concedido uma única vez quando uma atividade passa para concluída.';
$string['xpcourse'] = 'XP por conclusão do curso';
$string['xpcourse_desc'] = 'XP concedido uma única vez quando o Moodle registra a conclusão do curso.';
$string['xpforum'] = 'XP por postagem no fórum';
$string['xpforum_desc'] = 'XP concedido por participação no fórum. Um limite diário evita fazenda de pontos.';
$string['xpperminute'] = 'XP base por minuto estimado';
$string['xpperminute_desc'] = 'XP base usado antes do multiplicador de dificuldade. Os multiplicadores pertencem ao plugin, não à IA.';
$string['xpquiz'] = 'XP por envio de tentativa no questionário';
$string['xpquiz_desc'] = 'XP concedido ao enviar uma tentativa. Premia participação e não depende da nota.';
