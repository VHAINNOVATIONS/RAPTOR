using System;
using System.Collections;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhieNoteDao : INoteDao
    {
        VistaNoteDao vistaDao = null;

        public FhieNoteDao(AbstractConnection cxn)
        {
            vistaDao = new VistaNoteDao(cxn);
        }

        public Dictionary<string, ArrayList> getNoteTitles(string target, string direction)
        {
            return null;
        }

        public Note[] getSignedNotes(string fromDate, string toDate, int nNotes)
        {
            return vistaDao.getNotes(fromDate, toDate, nNotes);
        }

        public Note[] getUnsignedNotes(string fromDate, string toDate, int nNotes)
        {
            return null;
        }

        public Note[] getUncosignedNotes(string fromDate, string toDate, int nNotes)
        {
            return null;
        }

        public Note[] getNotes(string fromDate, string toDate, int nNotes)
        {
            return vistaDao.getNotes(fromDate, toDate, nNotes);
        }

        public Note[] getDischargeSummaries(string fromDate, string toDate, int nNotes)
        {
            return vistaDao.getDischargeSummaries(fromDate, toDate, nNotes);
        }

        public string getNoteText(String noteId)
        {
            return null;
        }

        public bool isSurgeryNote(string noteId)
        {
            return false;
        }

        public bool isOneVisitNote(string docDefId, string dfn, Encounter encounter)
        {
            return false;
        }

        public bool isOneVisitNote(string docDefId, string dfn, string visitStr)
        {
            return false;
        }

        public bool isConsultNote(string noteId)
        {
            return false;
        }

        public NoteResult writeNote(
            string titleId,
            Encounter encounter,
            string text,
            string authorId,
            string cosignerId,
            string consultId,
            string prfId)
        {
            return null;
        }

        public string getCrisisNotes(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public bool isCosignerRequired(string userId, string noteId)
        {
            return false;
        }

        public bool isCosignerRequired(string userId, string authorId, string noteId)
        {
            return false;
        }

        public string getNoteEncounterString(string noteId)
        {
            return null;
        }

        public bool isPrfNote(string noteId)
        {
            return false;
        }

        public PatientRecordFlag[] getPrfNoteActions(string noteId)
        {
            return null;
        }

        public string signNote(string noteId, string userId, string esig)
        {
            return null;
        }

        public string closeNote(string noteIEN, string consultIEN)
        {
            return null;
        }

        public string getClinicalWarnings(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getAdvanceDirectives(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

    }
}
