using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class Folder : BaseModel
    {
        private string _name;

        public string Name
        {
            get { return _name; }
            set { _name = value; }
        }
        private gov.va.medora.mdo.domain.sm.User _owner;

        public gov.va.medora.mdo.domain.sm.User Owner
        {
            get { return _owner; }
            set { _owner = value; }
        }
        private MessagesPage _messages;

        public MessagesPage Messages
        {
            get { return _messages; }
            set { _messages = value; }
        }
        private int _count;

        public int Count
        {
            get { return _count; }
            set { _count = value; }
        }
        private int _unreadCount;

        public int UnreadCount
        {
            get { return _unreadCount; }
            set { _unreadCount = value; }
        }
        private bool _systemFolder;

        public bool SystemFolder
        {
            get { return Enum.IsDefined(typeof(gov.va.medora.mdo.domain.sm.enums.SystemFolderEnum), this.Id); }
            set { _systemFolder = value; }
        }
        //private MessageFilterEnum _filter;

        //protected MessageFilterEnum Filter
        //{
        //    get { return _filter; }
        //    set { _filter = value; }
        //}
        //private MessagesOrderByEnum _orderBy;

        //protected MessagesOrderByEnum OrderBy
        //{
        //    get { return _orderBy; }
        //    set { _orderBy = value; }
        //}
        //private SortOrderEnum _sortOrder;

        //protected SortOrderEnum SortOrder
        //{
        //    get { return _sortOrder; }
        //    set { _sortOrder = value; }
        //}
        private List<SubFolder> _subfolderList;

        public List<SubFolder> SubfolderList
        {
            get { return _subfolderList; }
            set { _subfolderList = value; }
        }
        private Int64 _currentSubFolderId;

        public Int64 CurrentSubFolderId
        {
            get { return _currentSubFolderId; }
            set { _currentSubFolderId = value; }
        }

        internal static Folder getFolderFromReader(System.Data.IDataReader rdr)
        {
            return getFolderFromReader(rdr, mdo.dao.oracle.mhv.sm.QueryUtils.getColumnExistsTable(gov.va.medora.mdo.dao.oracle.mhv.sm.TableSchemas.FOLDER_COLUMNS, rdr));
        }

        internal static Folder getFolderFromReader(System.Data.IDataReader rdr, Dictionary<string, bool> columnTable)
        {
            Folder folder = new Folder();

            if (columnTable["FOLDER_ID"])
            {
                int idIndex = rdr.GetOrdinal("FOLDER_ID");
                if (!rdr.IsDBNull(idIndex))
                {
                    folder.Id = Convert.ToInt32(rdr.GetDecimal(idIndex));
                }
            }
            if (columnTable["USER_ID"])
            {
                int userIdIndex = rdr.GetOrdinal("USER_ID");
                if (!rdr.IsDBNull(userIdIndex))
                {
                    folder.Owner = new User() { Id = Convert.ToInt32(rdr.GetDecimal(userIdIndex)) };
                }
            }
            if (columnTable["FOLDER_NAME"])
            {
                int nameIndex = rdr.GetOrdinal("FOLDER_NAME");
                if (!rdr.IsDBNull(nameIndex))
                {
                    folder.Name = rdr.GetString(nameIndex);
                }
            }
            if (columnTable["FOLDOPLOCK"])
            {
                int oplockIndex = rdr.GetOrdinal("FOLDOPLOCK");
                if (!rdr.IsDBNull(oplockIndex))
                {
                    folder.Oplock = Convert.ToInt32(rdr.GetDecimal(oplockIndex));
                }
            }

            // set up other folder properties
            if (folder.Id <= 0)
            {
                folder.SystemFolder = true;
                folder.Name = Enum.GetName(typeof(domain.sm.enums.SystemFolderEnum), folder.Id);
            }
            else if (folder.Id > 0) // all system folders have ID < 0
            {
                if (columnTable["FOLDACTIVE"])
                {
                    folder.Active = rdr.GetDecimal(rdr.GetOrdinal("FOLDACTIVE")) == 1;
                }
            }

            return folder;
        }

    }
}
