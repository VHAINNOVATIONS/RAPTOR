using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.utils;
using System.Collections;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.rdw;
using System.Net;
using System.IO;

namespace gov.va.medora.mdo.dao.soap.rdw
{
    public class RdwNoteDao : INoteDao
    {
        RdwConnection _cxn;

        public RdwNoteDao(AbstractConnection cxn)
        {
            if (!(cxn is RdwConnection))
            {
                throw new MdoException("Invalid connection");
            }
            _cxn = (RdwConnection)cxn;
        }

        public Note[] getNotes(string fromDate, string toDate, int nNotes)
        {
            MDWSRPCs rdw = new MDWSRPCs() { Url = _cxn.DataSource.Provider }; // the cookie URL should have been set on the connection
            string response = rdw.getProgNotes(_cxn.Pid, nNotes, true, fromDate, toDate);
            return toNotes(response);
        }

        internal Note[] toNotes(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, '\n');
            ArrayList lst = new ArrayList();
            Note note = null;
            string scannedDoc = "";
            bool fInTextPortion = false;
            for (int i = 0; i < lines.Length; i++)
            {
                if (String.IsNullOrEmpty(lines[i]))
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                if (flds.Length == 1 && fInTextPortion)
                {
                    scannedDoc += lines[i] + "\n";
                    continue;
                }
                if (flds.Length <= 1)
                {
                    continue;
                }
                if (flds[1] == "[+]")
                {
                    note.Text = VistaUtils.removeCtlChars(note.Text);
                    lst.Add(note);
                    fInTextPortion = false;
                }
                else if (flds[0] == "1")
                {
                    fInTextPortion = false;
                    note = new Note();
                    string[] subflds = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                    if (subflds.Length == 2)
                    {
                        note.SiteId = new SiteId(subflds[1], subflds[0]);
                    }
                    else if (flds[1] != "")
                    {
                        note.SiteId = new SiteId(_cxn.DataSource.SiteId.Id, flds[1]);
                    }
                    else
                    {
                        note.SiteId = _cxn.DataSource.SiteId;
                    }
                }
                else if (flds[0] == "2")
                {
                    note.Id = flds[1];
                }
                else if (flds[0] == "3")
                {
                    note.Timestamp = VistaTimestamp.toUtcFromRdv(flds[1]);
                }
                else if (flds[0] == "4")
                {
                    note.LocalTitle = flds[1];
                }
                else if (flds[0] == "5")
                {
                    note.Author = new Author("", flds[1], "");
                }
                else if (flds[0] == "6")
                {
                    fInTextPortion = true;
                    if (!String.IsNullOrEmpty(scannedDoc))
                    {
                        note.Text += scannedDoc + "\n";
                        scannedDoc = "";
                    }
                    note.Text += flds[1] + "\n";
                    if (flds[1].StartsWith("STANDARD TITLE"))
                    {
                        string[] parts = StringUtils.split(flds[1], StringUtils.COLON);
                        note.StandardTitle = parts[1].Trim();
                    }
                }
            }
            Note[] notes = (Note[])lst.ToArray(typeof(Note));
            return notes;
        }

        #region Not Implemented Members
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

        public NoteResult writeNote(string titleId, Encounter encounter, string text, string authorId, string cosignerId, string consultId, string prfId)
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
