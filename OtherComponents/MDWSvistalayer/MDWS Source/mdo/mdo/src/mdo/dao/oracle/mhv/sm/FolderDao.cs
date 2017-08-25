using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Data;
using Oracle.DataAccess.Client;
using Oracle.DataAccess.Types;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo.dao.oracle.mhv.sm
{
    public class FolderDao
    {
        MdoOracleConnection _cxn;
        delegate OracleDataReader reader();
        delegate Int32 nonQuery();

        public FolderDao(AbstractConnection cxn)
        {
            _cxn = (MdoOracleConnection)cxn;
        }

        #region Folder CRUD
        #region Get Folder
        public domain.sm.Folder getFolder(Int32 folderId)
        {
            OracleQuery request = buildGetFolderQuery(folderId);
            reader rdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, rdr);
            return toFolder(response);
        }

        internal OracleQuery buildGetFolderQuery(Int32 folderId)
        {
            string sql = "SELECT FOLDER_ID, USER_ID, FOLDER_NAME, OPLOCK AS FOLDOPLOCK FROM SMS.FOLDER WHERE FOLDER_ID=:folderId AND ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter folderIdParam = new OracleParameter("folderId", OracleDbType.Decimal);
            folderIdParam.Value = folderId;
            query.Command.Parameters.Add(folderIdParam);

            return query;
        }
        #endregion

        #region Delete Folder
        public void deleteFolder(Int32 folderId)
        {
            OracleQuery request = buildDeleteFolderQuery(folderId);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 response = (Int32)_cxn.query(request, qry);

            if (response != 1)
            {
                throw new MdoException("Unable to delete folder");
            }
        }

        internal OracleQuery buildDeleteFolderQuery(Int32 folderId)
        {
            string sql = "DELETE FROM SMS.FOLDER WHERE FOLDER_ID=:folderId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter folderIdParam = new OracleParameter("folderId", OracleDbType.Decimal);
            folderIdParam.Value = folderId;
            query.Command.Parameters.Add(folderIdParam);

            return query;
        }
        #endregion

        #region Update Folder
        public domain.sm.Folder updateFolder(domain.sm.Folder folder)
        {
            OracleQuery request = buildUpdateFolderQuery(folder);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 response = (Int32)_cxn.query(request, qry);

            if (response != 1)
            {
                throw new MdoException("Unable to update folder");
            }
            folder.Oplock++;
            return folder;
        }

        internal OracleQuery buildUpdateFolderQuery(domain.sm.Folder folder)
        {
            string sql = "UPDATE SMS.FOLDER SET FOLDER_NAME=:folderName, OPLOCK=:oplockPlusOne, MODIFIED_DATE=SYSDATE WHERE FOLDER_ID=:folderId and OPLOCK=:oplock AND ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter folderNameParam = new OracleParameter("folderName", OracleDbType.Varchar2, 50);
            folderNameParam.Value = folder.Name;
            query.Command.Parameters.Add(folderNameParam);

            OracleParameter oplockPlusOneParam = new OracleParameter("oplockPlusOne", OracleDbType.Decimal);
            oplockPlusOneParam.Value = folder.Oplock + 1;
            query.Command.Parameters.Add(oplockPlusOneParam);

            //OracleParameter modifiedParam = new OracleParameter("modifiedDate", OracleDbType.Date);
            //modifiedParam.Value = new OracleDate(DateTime.Now);
            //query.Command.Parameters.Add(modifiedParam);

            OracleParameter folderIdParam = new OracleParameter("folderId", OracleDbType.Decimal);
            folderIdParam.Value = folder.Id;
            query.Command.Parameters.Add(folderIdParam);

            OracleParameter oplockParam = new OracleParameter("oplock", OracleDbType.Decimal);
            oplockParam.Value = folder.Oplock;
            query.Command.Parameters.Add(oplockParam);

            return query;
        }
        #endregion

        #region Create Folder
        public domain.sm.Folder createFolder(domain.sm.Folder folder)
        {
            OracleQuery request = buildCreateFolderQuery(folder);
            nonQuery qry = delegate() { return request.Command.ExecuteNonQuery(); };
            Int32 response = (Int32)_cxn.query(request, qry);

            if (response != 1)
            {
                throw new MdoException("Unable to create folder");
            }
            folder.Id = ((Oracle.DataAccess.Types.OracleDecimal)request.Command.Parameters["outId"].Value).ToInt32();
            return folder;
        }

        internal OracleQuery buildCreateFolderQuery(domain.sm.Folder folder)
        {
            string sql = "INSERT INTO SMS.FOLDER (USER_ID, FOLDER_NAME) VALUES (:userId, :folderName) RETURNING FOLDER_ID INTO :outId";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter userIdParam = new OracleParameter("userId", OracleDbType.Decimal);
            userIdParam.Value = folder.Owner.Id;
            query.Command.Parameters.Add(userIdParam);

            OracleParameter folderNameParam = new OracleParameter("folderName", OracleDbType.Varchar2, 50);
            folderNameParam.Value = folder.Name;
            query.Command.Parameters.Add(folderNameParam);

            OracleParameter outIdParam = new OracleParameter("outId", OracleDbType.Decimal);
            outIdParam.Direction = ParameterDirection.Output;
            query.Command.Parameters.Add(outIdParam);

            return query;
        }

        #endregion

        internal domain.sm.Folder toFolder(IDataReader rdr)
        {
            domain.sm.Folder folder = new domain.sm.Folder();

            if (rdr.Read())
            {
                folder = domain.sm.Folder.getFolderFromReader(rdr);
            }

            return folder;
        }
        #endregion

        public domain.sm.Addressee moveMessageToFolder(domain.sm.Addressee addressee, domain.sm.Folder newFolder)
        {
            domain.sm.Addressee dbAddressee = new AddresseeDao(_cxn).getAddressee(addressee.Id);
            if (dbAddressee == null || dbAddressee.Id <= 0)
            {
                throw new MdoException("Couldn't find that addressee record");
            }

            checkValidMove(Convert.ToInt32(dbAddressee.FolderId), Convert.ToInt32(newFolder.Id), addressee.Owner.Id);
            
            dbAddressee.FolderId = newFolder.Id;
            dbAddressee.Oplock = addressee.Oplock;
            return new AddresseeDao(_cxn).updateAddressee(dbAddressee);
        }

        private void checkValidMove(Int32 currentFolder, Int32 newFolder, Int32 userId)
        {
            if (currentFolder == newFolder)
            {
                throw new MdoException("Message is already located in that folder");
            }
            if (currentFolder == (Int32)domain.sm.enums.SystemFolderEnum.Drafts || newFolder == (Int32)domain.sm.enums.SystemFolderEnum.Drafts)
            {
                throw new MdoException("Can't move message out of or in to drafts folder");
            }
            if (currentFolder == (Int32)domain.sm.enums.SystemFolderEnum.Sent || newFolder == (Int32)domain.sm.enums.SystemFolderEnum.Sent)
            {
                throw new MdoException("Can't move message out of or in to sent folder");
            }
            if (newFolder > 0)
            {
                domain.sm.Folder folder = getUserFolder(userId, newFolder); // make sure user owns this folder
                if (folder != null && folder.Id > 0)
                {
                    // ok
                    return;
                }
            }
        }

        internal domain.sm.Folder getUserFolder(Int32 userId, Int32 folderId)
        {
            OracleQuery request = buildGetUserFoldersQuery(userId);
            reader rdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, rdr);
            if (response.Read())
            {
                return domain.sm.Folder.getFolderFromReader(response);
            }
            else
            {
                throw new MdoException("User does not own that folder");
            }
        }

        internal OracleQuery buildGetUserFolderQuery(Int32 userId, Int32 folderId)
        {
            string sql = "SELECT FOLDER_ID, FOLDER_NAME, OPLOCK AS FOLDOPLOCK FROM SMS.FOLDER WHERE USER_ID=:userId AND FOLDER_ID=:folderId AND ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter userIdParam = new OracleParameter("userId", OracleDbType.Decimal);
            userIdParam.Value = Convert.ToDecimal(userId);
            query.Command.Parameters.Add(userIdParam);

            OracleParameter folderIdParam = new OracleParameter("folderId", OracleDbType.Decimal);
            folderIdParam.Value = Convert.ToDecimal(folderId);
            query.Command.Parameters.Add(folderIdParam);

            return query;
        }

        internal IList<domain.sm.Folder> getUserFolders(Int32 userId)
        {
            OracleQuery request = buildGetUserFoldersQuery(userId);
            reader rdr = delegate() { return request.Command.ExecuteReader(); };
            OracleDataReader response = (OracleDataReader)_cxn.query(request, rdr);
            return toFolders(response);
        }

        internal OracleQuery buildGetUserFoldersQuery(Int32 userId)
        {
            string sql = "SELECT FOLDER_ID, FOLDER_NAME, OPLOCK AS FOLDOPLOCK FROM SMS.FOLDER WHERE USER_ID=:userId AND ACTIVE=1";

            OracleQuery query = new OracleQuery();
            query.Command = new OracleCommand(sql);

            OracleParameter userIdParam = new OracleParameter("userId", OracleDbType.Decimal);
            userIdParam.Value = Convert.ToDecimal(userId);
            query.Command.Parameters.Add(userIdParam);

            return query;
        }

        internal IList<domain.sm.Folder> toFolders(IDataReader rdr)
        {
            IList<domain.sm.Folder> folders = new List<domain.sm.Folder>();
            Dictionary<string, bool> folderColumns = QueryUtils.getColumnExistsTable(TableSchemas.FOLDER_COLUMNS, rdr);

            while (rdr.Read())
            {
                folders.Add(domain.sm.Folder.getFolderFromReader(rdr, folderColumns));
            }

            return folders;
        }

    }
}
