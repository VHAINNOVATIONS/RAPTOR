using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo
{
    public class Problem : Observation
    {
        string _resolvedDate;
        bool _removed;
        bool _verified;
        IList<Note> _comments;
        KeyValuePair<string, string> _acuity;
        string _icd;
        bool _isServiceConnected;
        string _id;
        string _status;
        string _providerNarrative;
        string _onsetDate;
        string _modifiedDate;
        string _exposures;
        string _noteNarrative;
        StringDictionary _orgProps;

        public Problem()
        {
        }

        public string ResolvedDate
        {
            get { return _resolvedDate; }
            set { _resolvedDate = value; }
        }

        public bool Removed
        {
            get { return _removed; }
            set { _removed = value; }
        }

        public bool Verified
        {
            get { return _verified; }
            set { _verified = value; }
        }

        public IList<Note> Comments
        {
            get { return _comments; }
            set { _comments = value; }
        }

        public KeyValuePair<string, string> Acuity
        {
            get { return _acuity; }
            set { _acuity = value; }
        }

        public string Icd
        {
            get { return _icd; }
            set { _icd = value; }
        }

        public bool IsServiceConnected
        {
            get { return _isServiceConnected; }
            set { _isServiceConnected = value; }
        }
        public string Id
        {
            get { return _id; }
            set { _id = value; }
        }

        public string Status
        {
            get { return _status; }
            set { _status = value; }
        }

        public string ProviderNarrative
        {
            get { return _providerNarrative; }
            set { _providerNarrative = value; }
        }

        public string OnsetDate
        {
            get { return _onsetDate; }
            set { _onsetDate = value; }
        }

        public string ModifiedDate
        {
            get { return _modifiedDate; }
            set { _modifiedDate = value; }
        }

        public string Exposures
        {
            get { return _exposures; }
            set { _exposures = value; }
        }

        public string NoteNarrative
        {
            get { return _noteNarrative; }
            set { _noteNarrative = value; }
        }

        public StringDictionary OrganizationProperties
        {
            get
            {
                if (_orgProps == null)
                {
                    _orgProps = new StringDictionary();
                }
                return _orgProps;
            }
            set { _orgProps = value; }
        }

        public string Priority { get; set; }
    }
}
