using System;
using System.Collections;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface INoteDao
    {
        Dictionary<string, ArrayList> getNoteTitles(string target, string direction);
        Note[] getSignedNotes(string fromDate, string toDate, int nNotes);
        Note[] getUnsignedNotes(string fromDate, string toDate, int nNotes);
        Note[] getUncosignedNotes(string fromDate, string toDate, int nNotes);
        Note[] getNotes(string fromDate, string toDate, int nNotes);
        Note[] getDischargeSummaries(string fromDate, string toDate, int nNotes);
        string getNoteText(String noteId);
        bool isOneVisitNote(string docDefId, string pid, Encounter encounter);
        bool isOneVisitNote(string docDefId, string pid, string visitStr);
        bool isSurgeryNote(string noteId);
        bool isConsultNote(string noteId);
        NoteResult writeNote(
            string titleId,
            Encounter encounter,
            string text,
            string authorId,
            string cosignerId,
            string consultId,
            string prfId);
        string getCrisisNotes(string fromDate, string toDate, int nrpts);
        bool isCosignerRequired(string userId, string noteId, string authorId = null);
        string getNoteEncounterString(string noteId);
        bool isPrfNote(string noteId);
        PatientRecordFlag[] getPrfNoteActions(string noteId);
        string signNote(string noteId, string userId, string esig);
        string closeNote(string noteId, string consultId);
        string getClinicalWarnings(string fromDate, string toDate, int nrpts);
        string getAdvanceDirectives(string fromDate, string toDate, int nrpts);
    }
}
