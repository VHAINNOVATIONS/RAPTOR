using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data.SqlClient;
using System.Data;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwNoteDao : INoteDao
    {
        CdwConnection _cxn;

        public CdwNoteDao(AbstractConnection cxn)
        {
            _cxn = (CdwConnection)cxn;    
        }

        public Note[] getNotes(string fromDate, string toDate, int nNotes)
        {
            SqlDataAdapter request = buildGetNotesQuery(_cxn.Pid, fromDate, toDate, nNotes);
            IDataReader response = (IDataReader)_cxn.query(request);
            return toNotesFromReader(response);
        }

        internal SqlDataAdapter buildGetNotesQuery(string pid, string fromDate, string toDate, int nNotes)
        {
            string queryStr = "SELECT NOTE.Sta3n, NOTE.AuthorStaffIEN, NOTE.EntryDateTime, NOTE.LocationIEN, NOTE.NoteTruncated, NOTE.ParentIEN, NOTE.ReleaseDate, NOTE.ReportText, " +
                "NOTE.SignatureDateTime, NOTE.TIUDocumentDefinitionIEN, NOTE.TIUDocumentIEN, NOTE.VisitIEN, NOTE_DEF.Abbreviation, NOTE_DEF.TIUDocumentDefinitionName, " +
                "NOTE_DEF.PrintName, NOTE_DEF.TIUDocumentDefinitionType, NOTE_DEF.VHAEnterpriseStandardTitleIEN, NOTE_TITLE.StandardTitle " +
                "FROM TIU.TIUReportText_v002 NOTE JOIN DIM.TIUDocumentDefinition NOTE_DEF ON NOTE.TIUDocumentDefinitionIEN = NOTE_DEF.TIUDocumentDefinitionIEN " +
                "AND NOTE.Sta3n = NOTE_DEF.Sta3n JOIN DIM.VHAEnterpriseStandardTitle NOTE_TITLE ON NOTE_DEF.VHAEnterpriseStandardTitleIEN = NOTE_TITLE.VHAEnterpriseStandardTitleIEN AND " +
                "NOTE_TITLE.Sta3n = NOTE.Sta3n WHERE NOTE.Sta3n=@siteId AND NOTE.PatientIEN=@patientId;";

            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand(queryStr);

            SqlParameter siteParam = new SqlParameter("@siteId", SqlDbType.Int);
            siteParam.Value = "504"; // TODO - see ticket 3038 - get rid of this arg
            adapter.SelectCommand.Parameters.Add(siteParam);

            SqlParameter patientIdParam = new SqlParameter("@patientId", SqlDbType.VarChar);
            patientIdParam.Value = pid;
            adapter.SelectCommand.Parameters.Add(patientIdParam);

            return adapter;
        }

        internal Note[] toNotesFromReader(IDataReader rdr)
        {
            IList<Note> result = new List<Note>();

            while (rdr.Read())
            {
                string author = Convert.ToString(rdr.IsDBNull(rdr.GetOrdinal("AuthorStaffIEN")) ? 0 : rdr.GetInt32(rdr.GetOrdinal("AuthorStaffIEN")));
                DateTime entered = rdr.IsDBNull(rdr.GetOrdinal("EntryDateTime")) ? new DateTime() : rdr.GetDateTime(rdr.GetOrdinal("EntryDateTime"));
                string location = Convert.ToString(rdr.IsDBNull(rdr.GetOrdinal("LocationIEN")) ? 0 : rdr.GetInt32(rdr.GetOrdinal("LocationIEN")));
                bool truncated = rdr.IsDBNull(rdr.GetOrdinal("NoteTruncated")) ? false : rdr.GetBoolean(rdr.GetOrdinal("NoteTruncated"));
                string parentIen = Convert.ToString(rdr.IsDBNull(rdr.GetOrdinal("ParentIEN")) ? 0 : rdr.GetInt32(rdr.GetOrdinal("ParentIEN")));
                DateTime released = rdr.IsDBNull(rdr.GetOrdinal("ReleaseDate")) ? new DateTime() : rdr.GetDateTime(rdr.GetOrdinal("ReleaseDate"));
                string text = rdr.IsDBNull(rdr.GetOrdinal("ReportText")) ? "" : rdr.GetString(rdr.GetOrdinal("ReportText"));
                DateTime signed = rdr.IsDBNull(rdr.GetOrdinal("SignatureDateTime")) ? new DateTime() : rdr.GetDateTime(rdr.GetOrdinal("SignatureDateTime"));
                string documentDefinition = Convert.ToString(rdr.IsDBNull(rdr.GetOrdinal("TIUDocumentDefinitionIEN")) ? 0 : rdr.GetInt32(rdr.GetOrdinal("TIUDocumentDefinitionIEN")));
                string noteId = Convert.ToString(rdr.IsDBNull(rdr.GetOrdinal("TIUDocumentIEN")) ? 0 : rdr.GetInt32(rdr.GetOrdinal("TIUDocumentIEN")));
                string visit = Convert.ToString(rdr.IsDBNull(rdr.GetOrdinal("VisitIEN")) ? 0 : rdr.GetInt32(rdr.GetOrdinal("VisitIEN")));
                string abbreviation = rdr.IsDBNull(rdr.GetOrdinal("Abbreviation")) ? "" : rdr.GetString(rdr.GetOrdinal("Abbreviation"));
                string documentDefinitionName = rdr.IsDBNull(rdr.GetOrdinal("TIUDocumentDefinitionName")) ? "" : rdr.GetString(rdr.GetOrdinal("TIUDocumentDefinitionName"));
                string printName = rdr.IsDBNull(rdr.GetOrdinal("PrintName")) ? "" : rdr.GetString(rdr.GetOrdinal("PrintName"));
                string documentType = rdr.IsDBNull(rdr.GetOrdinal("TIUDocumentDefinitionType")) ? "" : rdr.GetString(rdr.GetOrdinal("TIUDocumentDefinitionType"));
                string standardTitleIen = rdr.IsDBNull(rdr.GetOrdinal("VHAEnterpriseStandardTitleIEN")) ? "" : rdr.GetString(rdr.GetOrdinal("VHAEnterpriseStandardTitleIEN"));
                string standardTitle = rdr.IsDBNull(rdr.GetOrdinal("StandardTitle")) ? "" : rdr.GetString(rdr.GetOrdinal("StandardTitle"));
                string siteId = Convert.ToString(rdr.GetInt32(rdr.GetOrdinal("Sta3n")));

                Note newNote = new Note()
                {
                    Author = new Author() { Id = author },
                    DocumentDefinitionId = documentDefinition,
                    Location = new HospitalLocation() { Id = location },
                    HasAddendum = truncated,
                    Id = noteId,
                    LocalTitle = printName,
                    ParentId = (String.IsNullOrEmpty(parentIen) | String.Equals(parentIen, "0")) ? "" : parentIen,
                    SiteId = new SiteId() { Id = siteId },
                    StandardTitle = standardTitle,
                    Text = text,
                    Timestamp = entered.ToString()
                };

                result.Add(newNote);
            }

            Note[] notes = new Note[result.Count];
            result.CopyTo(notes, 0);
            return notes;
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
