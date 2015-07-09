using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.soap.sm
{
    public class SmNoteDao : INoteDao
    {
        AbstractConnection _cxn;
        gov.va.medora.mdo.sm.tiu.TIUService _svc;

        public SmNoteDao(AbstractConnection cxn)
        {
            _cxn = cxn;
            _svc = new mdo.sm.tiu.TIUService();
            _svc.Url = cxn.DataSource.ConnectionString;
        }

        public NoteResult writeNote(string titleId, Encounter encounter, string text, string authorId, string cosignerId, string consultId, string prfId)
        {
            throw new NotImplementedException();
        }

        public NoteResult writeNote(Patient patient, User author, Note note, Encounter encounter, string sitecode)
        {
            mdo.sm.tiu.Patient p = new mdo.sm.tiu.Patient();

            DateTime trashDOB = new DateTime();
            if (DateTime.TryParse(patient.DOB, out trashDOB))
            {
                p.DateOfBirth = trashDOB;
            }
            Decimal trashDfn = new Decimal();
            if (Decimal.TryParse(patient.LocalPid, out trashDfn))
            {
                p.DFN = trashDfn;
            }
            p.FirstName = patient.Name.Firstname;
            p.LastName = patient.Name.Lastname;
            p.ICN = patient.MpiPid;
            Decimal trashSSN = new Decimal();
            if (Decimal.TryParse(patient.SSN.ToString(), out trashSSN))
            {
                p.SSN = trashSSN;
            }

            mdo.sm.tiu.Document n = new mdo.sm.tiu.Document();
            
            n.Author = new mdo.sm.tiu.Staff();
            n.Author.DUZ = 0;
            n.Author.FirstName = "";
            n.Author.LastName = "";
            n.Author.SSN = 0;

            DateTime trashNoteDateTime = new DateTime();
            if (DateTime.TryParse(note.Timestamp, out trashNoteDateTime))
            {
                n.DateTime = trashNoteDateTime;
            }
            n.NoteTitle = note.StandardTitle;
            n.Text = note.Text;
            DateTime trashEncounterDateTime = new DateTime();
            if (DateTime.TryParse(encounter.Timestamp, out trashEncounterDateTime))
            {
                n.VisitDateTime = trashEncounterDateTime;
            }
            

            mdo.sm.tiu.AckType response = _svc.postNote(p, n, sitecode);

            // TODO - what is an expected response???
            if (String.IsNullOrEmpty(response.Status))
            {

            }

            NoteResult result = new NoteResult();
            result.Explanation = response.Status;
            return result;
        }

        #region Not Implemented
        public Dictionary<string, System.Collections.ArrayList> getNoteTitles(string target, string direction)
        {
            throw new NotImplementedException();
        }

        public Note[] getSignedNotes(string fromDate, string toDate, int nNotes)
        {
            throw new NotImplementedException();
        }

        public Note[] getUnsignedNotes(string fromDate, string toDate, int nNotes)
        {
            throw new NotImplementedException();
        }

        public Note[] getUncosignedNotes(string fromDate, string toDate, int nNotes)
        {
            throw new NotImplementedException();
        }

        public Note[] getNotes(string fromDate, string toDate, int nNotes)
        {
            throw new NotImplementedException();
        }

        public Note[] getDischargeSummaries(string fromDate, string toDate, int nNotes)
        {
            throw new NotImplementedException();
        }

        public string getNoteText(string noteId)
        {
            throw new NotImplementedException();
        }

        public bool isOneVisitNote(string docDefId, string pid, Encounter encounter)
        {
            throw new NotImplementedException();
        }

        public bool isOneVisitNote(string docDefId, string pid, string visitStr)
        {
            throw new NotImplementedException();
        }

        public bool isSurgeryNote(string noteId)
        {
            throw new NotImplementedException();
        }

        public bool isConsultNote(string noteId)
        {
            throw new NotImplementedException();
        }


        public string getCrisisNotes(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public bool isCosignerRequired(string userId, string noteId, string authorId = null)
        {
            throw new NotImplementedException();
        }

        public string getNoteEncounterString(string noteId)
        {
            throw new NotImplementedException();
        }

        public bool isPrfNote(string noteId)
        {
            throw new NotImplementedException();
        }

        public PatientRecordFlag[] getPrfNoteActions(string noteId)
        {
            throw new NotImplementedException();
        }

        public string signNote(string noteId, string userId, string esig)
        {
            throw new NotImplementedException();
        }

        public string closeNote(string noteId, string consultId)
        {
            throw new NotImplementedException();
        }

        public string getClinicalWarnings(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }

        public string getAdvanceDirectives(string fromDate, string toDate, int nrpts)
        {
            throw new NotImplementedException();
        }
        #endregion

    }
}
